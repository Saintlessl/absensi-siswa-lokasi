<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TahunAjaranManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic user roles
        $this->superadmin = User::factory()->create([
            'role'  => 'superadmin',
            'email' => 'superadmin@myskanic.test',
        ]);

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin@myskanic.test',
        ]);

        $this->guru = User::factory()->create([
            'role'  => 'guru',
            'email' => 'guru@myskanic.test',
        ]);

        $this->siswa = User::factory()->create([
            'role'  => 'siswa',
            'email' => 'siswa@myskanic.test',
        ]);
    }

    public function test_superadmin_and_admin_can_access_academic_year_index(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('academic.tahun-ajaran.index'));

        $response->assertStatus(200);
        $response->assertSee('Master Tahun Ajaran');
        $response->assertSee('2026/2027');
        $response->assertSee('Tambah Tahun Ajaran');
        $response->assertSee('modal-tambahta');

        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('academic.tahun-ajaran.index'));

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Tambah Tahun Ajaran');
    }

    public function test_non_admin_cannot_access_academic_year_management(): void
    {
        $responseGuru = $this->actingAs($this->guru)
            ->get(route('academic.tahun-ajaran.index'));
        $responseGuru->assertRedirect(route('dashboard'));
        $responseGuru->assertSessionHas('error');

        $responseSiswa = $this->actingAs($this->siswa)
            ->get(route('academic.tahun-ajaran.index'));
        $responseSiswa->assertRedirect(route('dashboard'));
        $responseSiswa->assertSessionHas('error');
    }

    public function test_store_new_academic_year_with_validation(): void
    {
        $payload = [
            'tahun_mulai'     => 2028,
            'tahun_selesai'   => 2029,
            'nama'            => '2028/2029',
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2028-07-15',
            'tanggal_selesai' => '2029-06-30',
            'status'          => 'draft',
            'catatan'         => 'Rencana TA 2028/2029',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.store'), $payload);

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tahun_ajarans', [
            'nama'   => '2028/2029',
            'status' => 'draft',
        ]);
    }

    public function test_duplicate_name_and_invalid_dates_fail_validation(): void
    {
        // Try duplicate of 2026/2027
        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.store'), [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'nama'            => '2026/2027',
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-30',
            ]);

        $response->assertSessionHasErrors(['nama']);

        // Try end year <= start year
        $responseInvalid = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.store'), [
                'tahun_mulai'     => 2029,
                'tahun_selesai'   => 2028,
                'nama'            => '2029/2028',
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2029-07-15',
                'tanggal_selesai' => '2028-06-30',
            ]);

        $responseInvalid->assertSessionHasErrors(['tahun_selesai', 'tanggal_selesai']);
    }

    public function test_single_active_period_enforcement(): void
    {
        // Current active should be 2026/2027
        $activeTa = TahunAjaran::where('is_active', true)->first();
        $this->assertNotNull($activeTa);
        $this->assertEquals('2026/2027', $activeTa->nama);

        // Find or create another period 2027/2028
        $targetTa = TahunAjaran::where('nama', '2027/2028')->first();
        $this->assertFalse($targetTa->is_active);

        // Activate 2027/2028
        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.activate', $targetTa->id));

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $response->assertSessionHas('success');

        // Verify that 2027/2028 is now the ONLY active period
        $this->assertTrue($targetTa->fresh()->is_active);
        $this->assertEquals('aktif', $targetTa->fresh()->status);

        $this->assertFalse($activeTa->fresh()->is_active);
        $this->assertEquals('selesai', $activeTa->fresh()->status);

        $this->assertEquals(1, TahunAjaran::where('is_active', true)->count());
    }

    public function test_toggle_semester_switches_active_semester(): void
    {
        $activeTa = TahunAjaran::where('is_active', true)->first();
        $this->assertEquals('ganjil', $activeTa->semester_aktif);

        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.switch-semester', $activeTa->id));

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $this->assertEquals('genap', $activeTa->fresh()->semester_aktif);

        // Toggle back to ganjil
        $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.switch-semester', $activeTa->id));
        $this->assertEquals('ganjil', $activeTa->fresh()->semester_aktif);
    }

    public function test_cannot_delete_active_academic_year(): void
    {
        $activeTa = TahunAjaran::where('is_active', true)->first();

        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.destroy', $activeTa->id));

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('tahun_ajarans', ['id' => $activeTa->id]);
    }

    public function test_cannot_delete_academic_year_with_classes(): void
    {
        // Deactivate 2026/2027 first by activating 2027/2028
        $ta2027 = TahunAjaran::where('nama', '2027/2028')->first();
        $ta2027->activate();

        $ta2026 = TahunAjaran::where('nama', '2026/2027')->first();

        // Attach a class if not already attached
        Kelas::firstOrCreate(
            ['nama_kelas' => 'X PPLG 1 Test'],
            [
                'jurusan_id'      => null,
                'tingkat'         => 10,
                'nomor_rombel'    => 1,
                'tahun_ajaran'    => '2026/2027',
                'tahun_ajaran_id' => $ta2026->id,
                'jumlah_siswa'    => '36',
                'is_active'       => true,
            ]
        );

        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.destroy', $ta2026->id));

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('tahun_ajarans', ['id' => $ta2026->id]);
    }

    public function test_can_delete_empty_draft_academic_year(): void
    {
        $draft = TahunAjaran::create([
            'tahun_mulai'     => 2030,
            'tahun_selesai'   => 2031,
            'nama'            => '2030/2031',
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2030-07-15',
            'tanggal_selesai' => '2031-06-30',
            'is_active'       => false,
            'status'          => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('academic.tahun-ajaran.destroy', $draft->id));

        $response->assertRedirect(route('academic.tahun-ajaran.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tahun_ajarans', ['id' => $draft->id]);
    }
}
