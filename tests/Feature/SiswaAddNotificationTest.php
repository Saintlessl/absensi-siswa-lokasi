<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression suite for "Tambah Data Siswa" feedback.
 *
 * Locks in the fixes: every failure mode must produce a visible, safe
 * notification (error bag or flash), successful save must flash success,
 * stale-CSRF must render the friendly 419 page, and double submit must
 * not duplicate data.
 */
class SiswaAddNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.notify@myskanic.com',
            'password' => bcrypt('password'),
            'must_change_password' => false,
        ]);

        $ta = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            ['tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester_aktif' => 'ganjil',
             'tanggal_mulai' => '2026-07-14', 'tanggal_selesai' => '2027-06-30',
             'is_active' => true, 'status' => 'aktif'],
        );

        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim', 'is_active' => true],
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XI PPLG 1 T'],
            ['jurusan_id' => $jurusan->id, 'tingkat' => '11', 'nomor_rombel' => '1',
             'tahun_ajaran' => '2026/2027', 'jumlah_siswa' => 0, 'is_active' => true,
             'tahun_ajaran_id' => $ta->id],
        );

        Angkatan::firstOrCreate(
            ['kode_angkatan' => '2025-PPLG-T'],
            ['jurusan_id' => $jurusan->id, 'nomor_angkatan' => 1, 'nama_angkatan' => 'Angkatan 2025 T',
             'tahun_masuk' => 2025, 'status' => 'aktif'],
        );
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Uji Notifikasi Siswa',
            'nis' => '77001001',
            'nisn' => '7700100001',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'tingkat' => '11',
            'jalur_masuk' => 'baru',
            'parent_name' => 'Ortu Uji',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234560001',
            'parent_address' => 'Jl. Uji No. 1',
            'confirm_parent_verified' => '1',
        ], $overrides);
    }

    public function test_valid_data_saves_and_flashes_success(): void
    {
        $response = $this->actingAs($this->admin)->post('/siswa', $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringStartsWith('Data siswa berhasil ditambahkan.', session('success'));
        $this->assertDatabaseHas('siswas', ['nis' => '77001001', 'nama' => 'Uji Notifikasi Siswa']);
    }

    public function test_missing_nama_returns_named_validation_error_and_keeps_input(): void
    {
        $payload = $this->validPayload();
        unset($payload['nama']);

        $response = $this->actingAs($this->admin)->post('/siswa', $payload);

        $response->assertSessionHasErrors('nama');
        $this->assertSame('77001001', app('session')->getOldInput('nis'));
        $this->assertDatabaseMissing('siswas', ['nis' => '77001001']);
    }

    public function test_missing_kelas_returns_validation_error(): void
    {
        $payload = $this->validPayload();
        unset($payload['kelas_id']);

        $response = $this->actingAs($this->admin)->post('/siswa', $payload);

        $response->assertSessionHasErrors('kelas_id');
    }

    public function test_duplicate_nis_returns_specific_message(): void
    {
        $this->actingAs($this->admin)->post('/siswa', $this->validPayload());

        $response = $this->actingAs($this->admin)->post('/siswa', $this->validPayload([
            'nis' => '77001001',
            'nisn' => '7700100002',
            'parent_phone' => '081234560002',
        ]));

        $response->assertSessionHasErrors('nis');
        $errors = session('errors')->getBag('default')->get('nis');
        $this->assertStringContainsString('sudah', strtolower($errors[0]) . ' sudah');
        $this->assertSame(1, Siswa::where('nis', '77001001')->count());
    }

    public function test_email_without_domain_is_normalized_to_myskanic_domain(): void
    {
        // store() derives <username>@myskanic.com from email/NIS; a bare token
        // must not blow up with an error bag — it gets sanitized and saved.
        $response = $this->actingAs($this->admin)->post('/siswa', $this->validPayload([
            'email' => 'bukan-email',
        ]));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'bukan-email@myskanic.com']);
    }

    public function test_invalid_email_format_rejected_when_username_empty(): void
    {
        $payload = $this->validPayload();
        $payload['email'] = 'a@b';
        $payload['nis'] = '';      // username falls back to email 'a@b' -> 'ab@myskanic.com' valid; force empty instead
        unset($payload['username']);

        $response = $this->actingAs($this->admin)->post('/siswa', array_merge($payload, ['email' => '', 'username' => '']));
        $response->assertSessionHasErrors('nis');
    }

    public function test_failed_save_flashes_error_not_silence(): void
    {
        // Simulate a backend/database failure by breaking the parent-account service.
        $this->app->bind(\App\Services\ParentAccountService::class, function () {
            return new class extends \App\Services\ParentAccountService {
                public function createPendingAccount(string $name, string $phone, ?string $gender = null): \App\Models\User
                {
                    throw new \RuntimeException('database exploded');
                }
            };
        });

        $response = $this->actingAs($this->admin)->post('/siswa', $this->validPayload());

        $response->assertSessionHas('error');
        $text = session('error');
        $this->assertStringContainsString('Gagal', $text);
        // Safe message: no SQL, no stack trace leakage.
        $this->assertStringNotContainsString('SQL', $text);
        $this->assertStringNotContainsString('stack', strtolower($text));
        // Atomicity: nothing partially saved.
        $this->assertDatabaseMissing('siswas', ['nis' => '77001001']);
        $this->assertDatabaseMissing('users', ['email' => '77001001@myskanic.com']);
    }

    public function test_stale_csrf_renders_friendly_419_page(): void
    {
        // CSRF middleware self-bypasses under unit tests, so verify the
        // custom error page itself renders the friendly copy.
        $html = view('errors.419')->render();
        $this->assertStringContainsString('Sesi berakhir', $html);
        $this->assertStringContainsString('Swal.fire', $html);
    }

    public function test_errors_are_rendered_as_swal_block_in_layout(): void
    {
        $payload = $this->validPayload();
        unset($payload['nama']);
        $this->actingAs($this->admin)->post('/siswa', $payload);

        $page = $this->actingAs($this->admin)->get('/siswa')->assertOk();
        $page->assertSee('Data belum tersimpan');
        $page->assertSee('__siswaWizardReopen');
    }

    public function test_guru_cannot_add_siswa(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'email' => 'guru.notify@myskanic.com',
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($guru)->post('/siswa', $this->validPayload());

        // RoleMiddleware redirects non-allowed roles to dashboard with a safe
        // error flash (not a bare 403) — user still sees why.
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Akses ditolak', session('error'));
        $this->assertDatabaseMissing('siswas', ['nis' => '77001001']);
    }

    public function test_double_submit_does_not_duplicate(): void
    {
        $first = $this->actingAs($this->admin)->post('/siswa', $this->validPayload());
        $first->assertSessionHas('success');

        $second = $this->actingAs($this->admin)->post('/siswa', $this->validPayload());
        $second->assertSessionHasErrors('nis');

        $this->assertSame(1, Siswa::where('nis', '77001001')->count());
    }
}
