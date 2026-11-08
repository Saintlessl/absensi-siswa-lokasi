<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\StudentImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected TahunAjaran $ta;
    protected Jurusan $jurusan;
    protected Angkatan $angkatan;
    protected Kelas $kelas;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ta = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
                'semester_aktif' => 'ganjil',
                'tanggal_mulai' => '2026-07-15',
                'tanggal_selesai' => '2027-06-25',
                'is_active' => true,
                'status' => 'aktif',
            ]
        );
        $this->ta->update(['is_active' => true, 'status' => 'aktif']);

        $this->jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim', 'is_active' => true]
        );

        $this->angkatan = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusan->id],
            [
                'nomor_angkatan' => 19,
                'nama_angkatan' => 'Angkatan 2026',
                'kode_angkatan' => 'AKT-PPLG-2026',
                'tahun_kelulusan' => 2029,
                'status' => 'aktif',
                'is_active' => true,
            ]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta->id],
            [
                'tingkat' => 10,
                'nomor_rombel' => 1,
                'jurusan_id' => $this->jurusan->id,
                'angkatan_id' => $this->angkatan->id,
                'tahun_ajaran' => '2026/2027',
                'is_active' => true,
            ]
        );

        $this->adminUser = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'must_change_password' => false,
        ]);
    }

    public function test_manual_student_registration_sets_must_change_password_to_true(): void
    {
        $payload = [
            'nisn'                    => '0098765432',
            'nis'                     => '262710001',
            'nama'                    => 'Budi Santoso',
            'jenis_kelamin'           => 'L',
            'tahun_ajaran_id'         => $this->ta->id,
            'jalur_masuk'             => 'baru',
            'jurusan_id'              => $this->jurusan->id,
            'angkatan_id'             => $this->angkatan->id,
            'tingkat'                 => '10',
            'kelas_id'                => $this->kelas->id,
            'email'                   => 'budi.santoso@siswa.smkn1ciomas.sch.id',
            'password'                => 'password123',
            'parent_name'             => 'Santoso Senior',
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '081234567890',
            'parent_address'          => 'Jl. Raya Ciomas No. 10',
            'confirm_parent_verified' => 'on',
        ];

        $res = $this->actingAs($this->adminUser)->post(route('siswa.store'), $payload);
        $res->assertSessionHasNoErrors();

        $siswa = Siswa::where('nis', '262710001')->first();
        $this->assertNotNull($siswa);
        $user = $siswa->user;
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->must_change_password, 'Newly created student user MUST have must_change_password = true');
    }

    public function test_excel_import_sets_must_change_password_to_true_for_active_students(): void
    {
        $importService = app(StudentImportService::class);

        $rows = [
            [
                'row_number'            => 2,
                'nisn'                  => '0098765433',
                'nis'                   => '26271002',
                'nama'                  => 'Citra Lestari',
                'email'                 => 'citra.lestari@siswa.smkn1ciomas.sch.id',
                'jenis_kelamin'         => 'P',
                'tempat_lahir'          => 'Bogor',
                'tanggal_lahir'         => '2010-05-12',
                'jurusan_id'            => $this->jurusan->id,
                'angkatan_id'           => $this->angkatan->id,
                'kelas_id'              => $this->kelas->id,
                'tingkat'               => 10,
                'tahun_masuk'           => 2026,
                'tahun_ajaran_masuk_id' => $this->ta->id,
                'tahun_ajaran_masuk'    => '2026/2027',
                'tahun_ajaran_enr_id'   => $this->ta->id,
                'tahun_ajaran_enr'      => '2026/2027',
                'jalur_masuk'           => 'baru',
                'asal_sekolah'          => 'SMPN 1 Ciomas',
                'status'                => 'aktif',
                'tanggal_kelulusan'     => null,
                'parent_name'           => null,
                'errors'                => [],
            ]
        ];

        $batch = $importService->executeImport($rows, $this->adminUser->id);
        $this->assertEquals(1, $batch->successful_rows);

        $user = User::where('email', 'citra.lestari@siswa.smkn1ciomas.sch.id')->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool) $user->must_change_password, 'Imported active student user MUST have must_change_password = true');
    }

    public function test_user_with_must_change_password_is_redirected_to_profile(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $res = $this->actingAs($studentUser)->get(route('dashboard'));

        $res->assertRedirect(route('profile'));
        $res->assertSessionHas('warning');
    }

    public function test_user_with_must_change_password_can_access_profile_without_redirect_loop(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $res = $this->actingAs($studentUser)->get(route('profile'));

        $res->assertOk();
        $res->assertSee('Wajib Ganti Password');
        $res->assertSee('Ubah Password');
    }

    public function test_user_with_must_change_password_can_logout(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $res = $this->actingAs($studentUser)->post(route('proseslogout'));

        $res->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_json_requests_are_blocked_with_403_when_must_change_password_is_true(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $res = $this->actingAs($studentUser)->getJson(route('dashboard'));

        $res->assertStatus(403);
        $res->assertJsonFragment(['must_change_password' => true]);
    }

    public function test_wrong_current_password_fails_and_keeps_must_change_password_true(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $res = $this->actingAs($studentUser)->post(route('profile.password'), [
            'current_password' => 'wrong_password',
            'password' => 'NewSecret2026!',
            'password_confirmation' => 'NewSecret2026!',
        ]);

        $res->assertSessionHasErrors(['current_password']);
        $studentUser->refresh();
        $this->assertTrue((bool) $studentUser->must_change_password);
    }

    public function test_updating_password_resets_must_change_password_and_unlocks_dashboard(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Baru',
            'email' => 'siswabaru@smkn1ciomas.sch.id',
            'password' => Hash::make('defaultPassword123'),
            'role' => 'siswa',
            'must_change_password' => true,
        ]);

        $siswa = Siswa::create([
            'user_id'               => $studentUser->id,
            'nama'                  => 'Siswa Baru',
            'nisn'                  => '0098765499',
            'nis'                   => '26271099',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelas->id,
            'angkatan_id'           => $this->angkatan->id,
            'jurusan_id'            => $this->jurusan->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $this->ta->id,
            'tahun_ajaran_id'       => $this->ta->id,
            'status'                => 'aktif',
            'biometric_status'      => 'enrolled',
        ]);

        // Mock biometric face template so biometric middleware lets them into dashboard
        SiswaFaceBiometric::create([
            'siswa_id'              => $siswa->id,
            'face_descriptor'       => json_encode(array_fill(0, 128, 0.05)),
            'enrollment_photo_path' => 'biometrics/test.jpg',
            'enrollment_status'     => 'enrolled',
            'enrolled_at'           => now(),
            'is_active'             => true,
        ]);

        // 1. Initially blocked from dashboard
        $blocked = $this->actingAs($studentUser)->get(route('dashboard'));
        $blocked->assertRedirect(route('profile'));

        // 2. Change password
        $res = $this->actingAs($studentUser)->post(route('profile.password'), [
            'current_password' => 'defaultPassword123',
            'password' => 'NewStrongPassword2026!',
            'password_confirmation' => 'NewStrongPassword2026!',
        ]);

        $res->assertSessionHas('success');

        $studentUser->refresh();
        $this->assertFalse((bool) $studentUser->must_change_password, 'must_change_password MUST be false after password change');
        $this->assertTrue(Hash::check('NewStrongPassword2026!', $studentUser->password));

        // 3. Now dashboard is unlocked and accessible
        $dashboardRes = $this->actingAs($studentUser)->get(route('dashboard'));
        $dashboardRes->assertOk();
    }

    public function test_existing_users_with_must_change_password_false_are_unaffected(): void
    {
        $teacherUser = User::create([
            'name' => 'Guru Lama',
            'email' => 'guru.lama@smkn1ciomas.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'must_change_password' => false,
        ]);

        $res = $this->actingAs($teacherUser)->get(route('dashboard'));
        $res->assertOk();
    }
}
