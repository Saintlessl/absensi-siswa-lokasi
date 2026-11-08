<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AngkatanManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;
    protected User $admin;
    protected User $guru;
    protected User $siswaUser;
    protected Jurusan $jurusan;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::firstOrCreate(
            ['email' => 'superadmin.cohort.test@myskanic.com'],
            ['name' => 'Superadmin Cohort Test', 'password' => bcrypt('password123'), 'role' => 'superadmin', 'status' => 'active']
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.cohort.test@myskanic.com'],
            ['name' => 'Admin Cohort Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->guru = User::firstOrCreate(
            ['email' => 'guru.cohort.test@myskanic.com'],
            ['name' => 'Guru Cohort Test', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $this->siswaUser = User::firstOrCreate(
            ['email' => 'siswa.cohort.test@myskanic.com'],
            ['name' => 'Siswa Cohort Test', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );

        $this->jurusan = Jurusan::first() ?? Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->kelas = Kelas::first() ?? Kelas::create([
            'nama_kelas'   => 'X PPLG 1',
            'tingkat'      => '10',
            'jurusan_id'   => $this->jurusan->id,
            'jumlah_siswa' => 0,
            'tahun_ajaran' => '2026/2027',
        ]);
    }

    /**
     * Test role-based access control for Angkatan management.
     */
    public function test_angkatan_access_authorization(): void
    {
        // Superadmin & Admin should be allowed
        $this->actingAs($this->superadmin)
            ->getJson(route('angkatan.index'))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->getJson(route('angkatan.index'))
            ->assertStatus(200);

        // Guru and Siswa should be forbidden (403)
        $this->actingAs($this->guru)
            ->getJson(route('angkatan.index'))
            ->assertStatus(403);

        $this->actingAs($this->siswaUser)
            ->getJson(route('angkatan.index'))
            ->assertStatus(403);
    }

    /**
     * Test creating an Angkatan with auto defaults.
     */
    public function test_store_angkatan_with_automatic_defaults(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $this->jurusan->id,
            'nomor_angkatan' => 88,
            'tahun_masuk'    => 2026,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'jurusan_id'             => $this->jurusan->id,
                    'nomor_angkatan'         => 88,
                    'nama_angkatan'          => "Angkatan 88 {$this->jurusan->kode_jurusan}",
                    'kode_angkatan'          => "AKT-{$this->jurusan->kode_jurusan}-88",
                    'tahun_masuk'            => 2026,
                    'tahun_ajaran_masuk'     => '2026/2027',
                    'tahun_lulus_rencana'    => 2029,
                    'tahun_ajaran_kelulusan' => '2028/2029',
                    'status'                 => 'aktif',
                ],
            ]);

        $this->assertDatabaseHas('angkatans', [
            'jurusan_id'     => $this->jurusan->id,
            'nomor_angkatan' => 88,
            'nama_angkatan'  => "Angkatan 88 {$this->jurusan->kode_jurusan}",
            'kode_angkatan'  => "AKT-{$this->jurusan->kode_jurusan}-88",
            'status'         => 'aktif',
        ]);
    }

    /**
     * Test validation prevents invalid or duplicate data.
     */
    public function test_store_angkatan_validates_required_and_unique_fields(): void
    {
        Angkatan::create([
            'jurusan_id'     => $this->jurusan->id,
            'nomor_angkatan' => 87,
            'nama_angkatan'  => 'Angkatan 87 PPLG',
            'kode_angkatan'  => 'AKT-PPLG-87',
            'tahun_masuk'    => 2025,
            'status'         => 'aktif',
        ]);

        // Attempt duplicate nomor_angkatan in same major
        $response = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $this->jurusan->id,
            'nomor_angkatan' => 87,
            'tahun_masuk'    => 2025,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nomor_angkatan']);

        // Attempt missing tahun_masuk
        $response2 = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $this->jurusan->id,
            'nomor_angkatan' => 86,
        ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['tahun_masuk']);
    }

    /**
     * Test updating an Angkatan.
     */
    public function test_update_angkatan(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan' => 85,
            'nama_angkatan'  => 'Angkatan 85 Original',
            'kode_angkatan'  => 'AKT-85',
            'tahun_masuk'    => 2024,
            'status'         => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('angkatan.update', $angkatan->id), [
            'nomor_angkatan' => 85,
            'nama_angkatan'  => 'Angkatan 85 Updated',
            'kode_angkatan'  => 'AKT-85',
            'tahun_masuk'    => 2024,
            'status'         => 'selesai',
            'keterangan'     => 'Kelulusan angkatan 85 telah selesai diproses.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_angkatan' => 'Angkatan 85 Updated',
                    'status'        => 'selesai',
                ],
            ]);

        $angkatan->refresh();
        $this->assertEquals('Angkatan 85 Updated', $angkatan->nama_angkatan);
        $this->assertEquals('selesai', $angkatan->status);
        $this->assertNotNull($angkatan->tanggal_kelulusan);
    }

    /**
     * Test real-time statistics calculation endpoint.
     */
    public function test_angkatan_realtime_stats_calculation(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan' => 84,
            'nama_angkatan'  => 'Angkatan 84',
            'kode_angkatan'  => 'AKT-84',
            'tahun_masuk'    => 2024,
            'status'         => 'aktif',
        ]);

        $u1 = User::create(['name' => 'S1', 'email' => 's1.test84@myskanic.com', 'password' => bcrypt('123'), 'role' => 'siswa']);
        $u2 = User::create(['name' => 'S2', 'email' => 's2.test84@myskanic.com', 'password' => bcrypt('123'), 'role' => 'siswa']);
        $u3 = User::create(['name' => 'S3', 'email' => 's3.test84@myskanic.com', 'password' => bcrypt('123'), 'role' => 'siswa']);

        Siswa::create(['user_id' => $u1->id, 'nis' => '8401', 'nama' => 'Siswa 1', 'angkatan_id' => $angkatan->id, 'tahun_masuk' => 2024, 'status' => 'aktif', 'kelas_id' => $this->kelas->id]);
        Siswa::create(['user_id' => $u2->id, 'nis' => '8402', 'nama' => 'Siswa 2', 'angkatan_id' => $angkatan->id, 'tahun_masuk' => 2024, 'status' => 'lulus', 'kelas_id' => $this->kelas->id]);
        Siswa::create(['user_id' => $u3->id, 'nis' => '8403', 'nama' => 'Siswa 3', 'angkatan_id' => $angkatan->id, 'tahun_masuk' => 2024, 'status' => 'pindah', 'kelas_id' => $this->kelas->id]);

        $response = $this->actingAs($this->admin)->getJson(route('angkatan.stats', $angkatan->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'angkatan_id'  => $angkatan->id,
                    'total_siswa'  => 3,
                    'siswa_aktif'  => 1,
                    'siswa_lulus'  => 1,
                    'siswa_pindah' => 1,
                ],
            ]);
    }

    /**
     * Test safe deletion guard: cannot delete cohort with registered students.
     */
    public function test_cannot_delete_angkatan_with_students(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan' => 83,
            'nama_angkatan'  => 'Angkatan 83',
            'kode_angkatan'  => 'AKT-83',
            'tahun_masuk'    => 2023,
            'status'         => 'aktif',
        ]);

        $u = User::create(['name' => 'Siswa Guard', 'email' => 'siswa.guard@myskanic.com', 'password' => bcrypt('123'), 'role' => 'siswa']);
        Siswa::create(['user_id' => $u->id, 'nis' => '8301', 'nama' => 'Siswa Guard', 'angkatan_id' => $angkatan->id, 'tahun_masuk' => 2023, 'status' => 'aktif', 'kelas_id' => $this->kelas->id]);

        $response = $this->actingAs($this->admin)->postJson(route('angkatan.destroy', $angkatan->id));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('angkatans', ['id' => $angkatan->id]);
    }

    /**
     * Test cohort without students can be safely deleted.
     */
    public function test_can_delete_empty_angkatan(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan' => 82,
            'nama_angkatan'  => 'Angkatan 82 Empty',
            'kode_angkatan'  => 'AKT-82',
            'tahun_masuk'    => 2022,
            'status'         => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('angkatan.destroy', $angkatan->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('angkatans', ['id' => $angkatan->id]);
    }

    /**
     * Test HTML views for index, edit, and show render without errors.
     */
    public function test_angkatan_views_render_properly(): void
    {
        $angkatan = Angkatan::create([
            'nomor_angkatan' => 81,
            'nama_angkatan'  => 'Angkatan 81 View Test',
            'kode_angkatan'  => 'AKT-81',
            'tahun_masuk'    => 2021,
            'status'         => 'selesai',
        ]);

        // Index view
        $this->actingAs($this->admin)
            ->get(route('angkatan.index'))
            ->assertStatus(200)
            ->assertSee('Daftar Angkatan')
            ->assertSee('Angkatan 81 View Test');

        // Show view
        $this->actingAs($this->admin)
            ->get(route('angkatan.show', $angkatan->id))
            ->assertStatus(200)
            ->assertSee('Angkatan 81 View Test')
            ->assertSee('Total Siswa');

        // Edit view (modal form)
        $this->actingAs($this->admin)
            ->post(route('angkatan.edit'), ['id' => $angkatan->id])
            ->assertStatus(200)
            ->assertSee('Perbarui Angkatan');
    }

    /**
     * Test major-specific cohorts architecture:
     * 1. Different majors can have different cohort numbers in the same entry year.
     * 2. Different majors can share the same cohort number independently.
     * 3. Same major cannot duplicate cohort number or entry year.
     */
    public function test_major_specific_cohort_architecture_and_duplicate_handling(): void
    {
        $pplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG_SPEC'],
            ['nama_jurusan' => 'PPLG Test Special', 'is_active' => true]
        );

        $animasi = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'ANIM_SPEC'],
            ['nama_jurusan' => 'Animasi Test Special', 'is_active' => true]
        );

        // 1. In same academic year (Masuk 2026), PPLG can be Angkatan 19 while Animasi is Angkatan 17
        $pplg19 = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $pplg->id,
            'nomor_angkatan' => 19,
            'tahun_masuk'    => 2026,
        ]);
        $pplg19->assertStatus(201);

        $anim17 = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $animasi->id,
            'nomor_angkatan' => 17,
            'tahun_masuk'    => 2026,
        ]);
        $anim17->assertStatus(201);

        // 2. Animasi and PPLG can BOTH have Angkatan 17 independently
        $pplg17 = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $pplg->id,
            'nomor_angkatan' => 17,
            'tahun_masuk'    => 2024,
        ]);
        $pplg17->assertStatus(201);

        // 3. Attempting to create duplicate Angkatan 17 in PPLG MUST FAIL (422)
        $duplicatePplg17 = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $pplg->id,
            'nomor_angkatan' => 17,
            'tahun_masuk'    => 2023,
        ]);
        $duplicatePplg17->assertStatus(422)
            ->assertJsonValidationErrors(['nomor_angkatan']);

        // 4. Attempting to create duplicate entry year in same major MUST FAIL (422)
        $duplicatePplgYear = $this->actingAs($this->admin)->postJson(route('angkatan.store'), [
            'jurusan_id'     => $pplg->id,
            'nomor_angkatan' => 99,
            'tahun_masuk'    => 2026, // already used by PPLG Angkatan 19
        ]);
        $duplicatePplgYear->assertStatus(422)
            ->assertJsonValidationErrors(['tahun_masuk']);
    }
}
