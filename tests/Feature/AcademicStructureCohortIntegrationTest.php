<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AcademicStructureCohortIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;
    protected User $admin;
    protected User $kaprog1;
    protected User $kaprog2;
    protected Jurusan $jurusan1;
    protected Jurusan $jurusan2;
    protected Kelas $kelas1;
    protected Kelas $kelas2;
    protected Angkatan $activeCohort;
    protected Angkatan $alumniCohort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::firstOrCreate(
            ['email' => 'superadmin.academic.test@myskanic.com'],
            ['name' => 'Superadmin Academic Test', 'password' => bcrypt('password123'), 'role' => 'superadmin', 'status' => 'active']
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.academic.test@myskanic.com'],
            ['name' => 'Admin Academic Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->jurusan1 = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG_TEST'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak Test', 'is_active' => true]
        );

        $this->jurusan2 = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'ANIM_TEST'],
            ['nama_jurusan' => 'Animasi Test', 'is_active' => true]
        );

        $this->kaprog1 = User::firstOrCreate(
            ['email' => 'kaprog1.test@myskanic.com'],
            ['name' => 'Kaprog PPLG Test', 'password' => bcrypt('password123'), 'role' => 'kaprog', 'status' => 'active', 'jurusan_id' => $this->jurusan1->id]
        );

        $this->kaprog2 = User::firstOrCreate(
            ['email' => 'kaprog2.test@myskanic.com'],
            ['name' => 'Kaprog Animasi Test', 'password' => bcrypt('password123'), 'role' => 'kaprog', 'status' => 'active', 'jurusan_id' => $this->jurusan2->id]
        );

        $this->kelas1 = Kelas::create([
            'nama_kelas'   => 'X PPLG TEST 1',
            'tingkat'      => '10',
            'jurusan_id'   => $this->jurusan1->id,
            'jumlah_siswa' => 0,
            'tahun_ajaran' => '2026/2027',
            'is_active'    => true,
        ]);

        $this->kelas2 = Kelas::create([
            'nama_kelas'   => 'XI ANIM TEST 1',
            'tingkat'      => '11',
            'jurusan_id'   => $this->jurusan2->id,
            'jumlah_siswa' => 0,
            'tahun_ajaran' => '2026/2027',
            'is_active'    => true,
        ]);

        $this->activeCohort = Angkatan::create([
            'nomor_angkatan'         => 79,
            'nama_angkatan'          => 'Angkatan 79 Aktif Test',
            'kode_angkatan'          => 'AKT-79',
            'tahun_masuk'            => 2026,
            'tahun_ajaran_masuk'     => '2026/2027',
            'tahun_lulus_rencana'    => 2029,
            'tahun_ajaran_kelulusan' => '2028/2029',
            'status'                 => 'aktif',
        ]);

        $this->alumniCohort = Angkatan::create([
            'nomor_angkatan'         => 78,
            'nama_angkatan'          => 'Angkatan 78 Alumni Test',
            'kode_angkatan'          => 'AKT-78',
            'tahun_masuk'            => 2023,
            'tahun_ajaran_masuk'     => '2023/2024',
            'tahun_lulus_rencana'    => 2026,
            'tahun_ajaran_kelulusan' => '2025/2026',
            'status'                 => 'selesai',
            'tanggal_kelulusan'      => '2026-05-15',
        ]);
    }

    protected function createStudent(string $name, string $nis, Angkatan $angkatan, Kelas $kelas, string $status = 'aktif'): Siswa
    {
        $user = User::create([
            'name'     => $name,
            'email'    => "std.{$nis}@myskanic.test",
            'password' => bcrypt('password123'),
            'role'     => 'siswa',
            'status'   => 'active',
        ]);

        return Siswa::create([
            'user_id'     => $user->id,
            'nis'         => $nis,
            'nama'        => $name,
            'angkatan_id' => $angkatan->id,
            'tahun_masuk' => $angkatan->tahun_masuk,
            'kelas_id'    => $kelas->id,
            'status'      => $status,
        ]);
    }

    /**
     * Test Overview Struktur Akademik (Level 1) renders Jurusans and excludes school-level cohorts per redesign.
     */
    public function test_academic_overview_index_renders_jurusans_and_active_and_alumni_cohorts(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.index'));

        $response->assertStatus(200)
            ->assertSee('Struktur Akademik')
            ->assertSee('Program Keahlian')
            ->assertSee($this->jurusan1->nama_jurusan)
            ->assertDontSee('ANGKATAN AKTIF (TINGKAT SEKOLAH)')
            ->assertDontSee('ANGKATAN ALUMNI / LULUS');
    }

    /**
     * Test Detail Jurusan (Level 2) renders both Active Tingkat (10, 11, 12) and Alumni Cohorts.
     */
    public function test_academic_major_renders_active_tingkat_and_alumni_cohorts(): void
    {
        $response = $this->actingAs($this->admin)->get(route('academic.major', $this->jurusan1));

        $response->assertStatus(200)
            ->assertSee('ANGKATAN AKTIF')
            ->assertSee('Kelas 10')
            ->assertSee('Kelas 11')
            ->assertSee('Kelas 12')
            ->assertSee('ANGKATAN ALUMNI / LULUS')
            ->assertSee($this->alumniCohort->nama_angkatan);
    }

    /**
     * Test alumni card statistics: only students with status 'lulus' are counted as graduated.
     * Students with tidak_lulus, pindah, or keluar are NOT counted as graduated.
     */
    public function test_alumni_card_counts_only_graduated_students(): void
    {
        // 2 students lulus
        $s1 = $this->createStudent('Alumni Lulus 1', '7801', $this->alumniCohort, $this->kelas1, 'lulus');
        $s2 = $this->createStudent('Alumni Lulus 2', '7802', $this->alumniCohort, $this->kelas1, 'lulus');

        // 1 student tidak lulus
        $s3 = $this->createStudent('Alumni Tidak Lulus', '7803', $this->alumniCohort, $this->kelas1, 'tidak_lulus');

        // 1 student pindah
        $s4 = $this->createStudent('Alumni Pindah', '7804', $this->alumniCohort, $this->kelas1, 'pindah');

        // 1 student keluar
        $s5 = $this->createStudent('Alumni Keluar', '7805', $this->alumniCohort, $this->kelas1, 'keluar');

        $cohortService = app(\App\Services\CohortManagementService::class);
        $stats = $cohortService->calculateStatsForMajor($this->alumniCohort, $this->jurusan1->id);

        $this->assertEquals(5, $stats['total_siswa']);
        $this->assertEquals(2, $stats['siswa_lulus']); // ONLY 2 students graduated
        $this->assertEquals(1, $stats['siswa_tidak_lulus']);
        $this->assertEquals(1, $stats['siswa_pindah']);
        $this->assertEquals(1, $stats['siswa_keluar']);

        // Verify in rendered HTML view
        $response = $this->actingAs($this->admin)->get(route('academic.major', $this->jurusan1));
        $response->assertStatus(200)
            ->assertSee('2')
            ->assertSee('Siswa Lulus');
    }

    /**
     * Test Alumni Detail page renders summary cards, student list, and works with status filter.
     */
    public function test_alumni_detail_page_loads_with_correct_students_and_filters(): void
    {
        $sLulus = $this->createStudent('Siswa Lulus Alpha', '7810', $this->alumniCohort, $this->kelas1, 'lulus');
        $sPindah = $this->createStudent('Siswa Pindah Beta', '7811', $this->alumniCohort, $this->kelas1, 'pindah');

        // Access alumni detail page
        $response = $this->actingAs($this->admin)->get(route('academic.alumni.show', $this->alumniCohort->id));

        $response->assertStatus(200)
            ->assertSee($this->alumniCohort->nama_angkatan)
            ->assertSee('Siswa Lulus Alpha')
            ->assertSee('Siswa Pindah Beta')
            ->assertSee('Total Siswa Terdata')
            ->assertSee('Lulus (Alumni)');

        // Filter status=lulus: should only show Siswa Lulus Alpha
        $responseFilterLulus = $this->actingAs($this->admin)->get(route('academic.alumni.show', [
            'angkatan' => $this->alumniCohort->id,
            'status'   => 'lulus',
        ]));

        $responseFilterLulus->assertStatus(200)
            ->assertSee('Siswa Lulus Alpha')
            ->assertDontSee('Siswa Pindah Beta');

        // Filter search=7811: should only show Siswa Pindah Beta
        $responseSearch = $this->actingAs($this->admin)->get(route('academic.alumni.show', [
            'angkatan' => $this->alumniCohort->id,
            'search'   => '7811',
        ]));

        $responseSearch->assertStatus(200)
            ->assertSee('Siswa Pindah Beta')
            ->assertDontSee('Siswa Lulus Alpha');
    }

    /**
     * Test historical enrollment relation: rombel terakhir resolves via StudentEnrollment.
     */
    public function test_historical_class_rombel_terakhir_uses_student_enrollments(): void
    {
        $siswa = $this->createStudent('Siswa Histori Test', '7820', $this->alumniCohort, $this->kelas1, 'lulus');

        // Create historical enrollment
        StudentEnrollment::create([
            'siswa_id'     => $siswa->id,
            'kelas_id'     => $this->kelas1->id,
            'tahun_ajaran' => '2025/2026',
            'started_at'   => '2025-07-15',
            'ended_at'     => '2026-05-15',
            'is_active'    => false,
        ]);

        $siswa->refresh();
        $this->assertNotNull($siswa->latestEnrollment);
        $this->assertEquals($this->kelas1->id, $siswa->rombel_terakhir->id);

        $response = $this->actingAs($this->admin)->get(route('academic.alumni.show', $this->alumniCohort->id));
        $response->assertStatus(200)
            ->assertSee('Histori Terverifikasi')
            ->assertSee($this->kelas1->nama_kelas);
    }

    /**
     * Test KAPROG scoping authorization on major alumni detail page.
     */
    public function test_kaprog_scoping_on_major_alumni_detail(): void
    {
        // Kaprog 1 (PPLG) can access their own major alumni
        $this->actingAs($this->kaprog1)
            ->get(route('academic.major.alumni', ['jurusan' => $this->jurusan1->id, 'angkatan' => $this->alumniCohort->id]))
            ->assertStatus(200);

        // Kaprog 1 cannot access Animasi major alumni (403)
        $this->actingAs($this->kaprog1)
            ->get(route('academic.major.alumni', ['jurusan' => $this->jurusan2->id, 'angkatan' => $this->alumniCohort->id]))
            ->assertStatus(403);
    }

    /**
     * Test export alumni PDF route produces a valid PDF download.
     */
    public function test_alumni_pdf_export_downloads_successfully(): void
    {
        $this->createStudent('Alumni PDF Test', '7830', $this->alumniCohort, $this->kelas1, 'lulus');

        $response = $this->actingAs($this->admin)
            ->get(route('academic.alumni.export_pdf', $this->alumniCohort->id));

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }
}
