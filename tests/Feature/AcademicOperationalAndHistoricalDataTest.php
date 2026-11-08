<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\StudentAcademicHistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicOperationalAndHistoricalDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Guru $guru;
    protected User $guruUser;

    protected TahunAjaran $ta2026;
    protected TahunAjaran $ta2027;

    protected Jurusan $jurusan;
    protected Angkatan $angkatan18;
    protected Angkatan $angkatan17;

    protected Kelas $kelas10Pplg1_2026;
    protected Kelas $kelas12Pplg1_2026;
    protected Kelas $kelas10Pplg1_2027;
    protected Kelas $kelas11Pplg1_2027;

    protected StudentAcademicHistoryService $historyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyService = app(StudentAcademicHistoryService::class);

        $this->admin = User::factory()->create([
            'name'  => 'Admin Dual Track',
            'role'  => 'admin',
            'email' => 'admin.dt@myskanic.test',
        ]);

        $this->guruUser = User::factory()->create([
            'name'  => 'Guru Pengajar DT',
            'role'  => 'guru',
            'email' => 'guru.dt@myskanic.test',
        ]);
        $this->guru = Guru::create([
            'user_id'   => $this->guruUser->id,
            'nama'      => 'Guru Pengajar DT',
            'nip'       => '198801012015011001',
            'is_active' => true,
        ]);

        $this->ta2026 = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai'     => 2026,
                'tahun_selesai'   => 2027,
                'semester_aktif'  => 'genap',
                'tanggal_mulai'   => '2026-07-15',
                'tanggal_selesai' => '2027-06-25',
                'is_active'       => true,
                'status'          => 'aktif',
            ]
        );

        $this->ta2027 = TahunAjaran::firstOrCreate(
            ['nama' => '2027/2028'],
            [
                'tahun_mulai'     => 2027,
                'tahun_selesai'   => 2028,
                'semester_aktif'  => 'ganjil',
                'tanggal_mulai'   => '2027-07-15',
                'tanggal_selesai' => '2028-06-25',
                'is_active'       => false,
                'status'          => 'draft',
            ]
        );

        $this->jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim']
        );

        $this->angkatan18 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2025, 'jurusan_id' => $this->jurusan->id],
            [
                'nama_angkatan'  => 'Angkatan 18 PPLG',
                'nomor_angkatan' => 18,
                'kode_angkatan'  => 'AKT-PPLG-18',
                'tahun_kelulusan'=> 2028,
                'status'         => 'aktif',
            ]
        );

        $this->angkatan17 = Angkatan::firstOrCreate(
            ['tahun_masuk' => 2024, 'jurusan_id' => $this->jurusan->id],
            [
                'nama_angkatan'  => 'Angkatan 17 PPLG',
                'nomor_angkatan' => 17,
                'kode_angkatan'  => 'AKT-PPLG-17',
                'tahun_kelulusan'=> 2027,
                'status'         => 'aktif',
            ]
        );

        $this->kelas10Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2026->nama,
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => 'Guru A',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-X-PPLG-1-2026-DT',
        ]);

        $this->kelas12Pplg1_2026 = Kelas::create([
            'nama_kelas'      => 'XII PPLG 1',
            'tingkat'         => 12,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2026->nama,
            'tahun_ajaran_id' => $this->ta2026->id,
            'wali_kelas'      => 'Guru C',
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-XII-PPLG-1-2026-DT',
        ]);

        $this->kelas10Pplg1_2027 = Kelas::create([
            'nama_kelas'      => 'X PPLG 1',
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2027->nama,
            'tahun_ajaran_id' => $this->ta2027->id,
            'wali_kelas'      => null,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-X-PPLG-1-2027-DT',
        ]);

        $this->kelas11Pplg1_2027 = Kelas::create([
            'nama_kelas'      => 'XI PPLG 1',
            'tingkat'         => 11,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran'    => $this->ta2027->nama,
            'tahun_ajaran_id' => $this->ta2027->id,
            'wali_kelas'      => null,
            'jumlah_siswa'    => 0,
            'is_active'       => true,
            'qr_token'        => 'QR-XI-PPLG-1-2027-DT',
        ]);
    }

    protected function createStudent(string $nama, string $nis, Kelas $kelas, TahunAjaran $ta, Angkatan $angkatan): Siswa
    {
        $user = User::factory()->create(['name' => $nama, 'role' => 'siswa', 'email' => "{$nis}@student.test"]);
        $siswa = Siswa::create([
            'user_id'               => $user->id,
            'nis'                   => $nis,
            'nisn'                  => '00' . $nis,
            'nama'                  => $nama,
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $kelas->id,
            'angkatan_id'           => $angkatan->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $ta->id,
            'tahun_ajaran_masuk'    => '2026/2027',
            'status'                => 'aktif',
            'jalur_masuk'           => 'baru',
            'tempat_lahir'          => 'Bogor',
            'tanggal_lahir'         => '2009-05-15',
        ]);

        StudentEnrollment::create([
            'siswa_id'                  => $siswa->id,
            'tahun_ajaran_id'           => $ta->id,
            'tahun_ajaran'              => $ta->nama,
            'kelas_id'                  => $kelas->id,
            'jurusan_id'                => $kelas->jurusan_id,
            'angkatan_id'               => $angkatan->id,
            'tingkat'                   => $kelas->tingkat ?: 10,
            'started_at'                => Carbon::parse('2026-07-15'),
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'wali_kelas_nama'           => $kelas->wali_kelas ?: 'Wali Default',
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        return $siswa;
    }

    /**
     * Test: Presensi operasional vs historis & pemindahan rombel.
     */
    public function test_operational_attendance_respects_new_rombel_and_preserves_history(): void
    {
        $siswa = $this->createStudent('Rizki Pindah Rombel', '90001', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        // 1. Record historical attendance in 2026/2027
        $pastDate = Carbon::parse('2026-09-10');
        $histAbsensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => $pastDate->toDateString(),
            'status'   => 'Hadir',
        ]);

        // 2. Promote student from X PPLG 1 (2026) to XI PPLG 1 (2027)
        $this->historyService->promoteStudent($siswa, $this->kelas11Pplg1_2027, $this->ta2027);
        $siswa->refresh();

        // 3. Verify historical attendance record is 100% preserved
        $this->assertDatabaseHas('absensis', [
            'id'       => $histAbsensi->id,
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-09-10',
            'status'   => 'Hadir',
        ]);

        // 4. In new academic year 2027/2028:
        // Teacher schedule for X PPLG 1 in 2027 must NOT include student
        $rosterOldClass = Siswa::where('status', 'aktif')->where('kelas_id', $this->kelas10Pplg1_2027->id)->pluck('id');
        $this->assertFalse($rosterOldClass->contains($siswa->id));

        // Teacher schedule for XI PPLG 1 in 2027 DOES include student
        $rosterNewClass = Siswa::where('status', 'aktif')->where('kelas_id', $this->kelas11Pplg1_2027->id)->pluck('id');
        $this->assertTrue($rosterNewClass->contains($siswa->id));
    }

    /**
     * Test: Alumni excluded from operational roster while retaining full history.
     */
    public function test_graduated_student_excluded_from_operational_rosters(): void
    {
        $alumni = $this->createStudent('Alamsyah Lulus', '90002', $this->kelas12Pplg1_2026, $this->ta2026, $this->angkatan17);

        // Record past attendance
        Absensi::create([
            'siswa_id' => $alumni->id,
            'tanggal'  => '2026-10-15',
            'status'   => 'Hadir',
        ]);

        // Graduate
        $this->historyService->graduateStudent($alumni, $this->ta2026);
        $alumni->refresh();

        $this->assertEquals('lulus', $alumni->status);

        // Not in active roster of any class
        $allActiveStudents = Siswa::where('status', 'aktif')->pluck('id');
        $this->assertFalse($allActiveStudents->contains($alumni->id));

        // Past attendance remains
        $this->assertEquals(1, Absensi::where('siswa_id', $alumni->id)->count());

        // Past enrollment remains with status lulus
        $this->assertEquals('lulus', $alumni->latestEnrollment->status_enrollment);
    }

    /**
     * Test: Parent portal multi-child access and data leakage prevention.
     */
    public function test_parent_portal_multi_child_and_zero_data_leakage(): void
    {
        $child1 = $this->createStudent('Anak Pertama (Kelas 11)', '90011', $this->kelas11Pplg1_2027, $this->ta2027, $this->angkatan18);
        $child2 = $this->createStudent('Anak Kedua (Alumni)', '90012', $this->kelas12Pplg1_2026, $this->ta2026, $this->angkatan17);
        $otherChild = $this->createStudent('Anak Orang Lain', '90099', $this->kelas10Pplg1_2026, $this->ta2026, $this->angkatan18);

        // Graduate child 2
        $this->historyService->graduateStudent($child2, $this->ta2026);

        // Parent User
        $parentUser = User::factory()->create(['name' => 'Bapak Joko', 'role' => 'orangtua', 'email' => 'joko@parent.test']);
        $orangTua = OrangTua::create([
            'user_id'       => $parentUser->id,
            'siswa_id'      => $child1->id,
            'nama'          => 'Bapak Joko',
            'telepon'       => '08123456789',
            'jenis_kelamin' => 'L',
        ]);

        // Link child1 and child2 to this parent
        $orangTua->siswa()->attach([$child1->id, $child2->id]);

        // Record attendance for both
        Absensi::create(['siswa_id' => $child1->id, 'tanggal' => date('Y-m-d'), 'status' => 'hadir']);
        Absensi::create(['siswa_id' => $child2->id, 'tanggal' => '2026-10-01', 'status' => 'hadir']);

        // 1. Parent accesses dashboard default (child1)
        $resDefault = $this->actingAs($parentUser)->get(route('dashboard'));
        $resDefault->assertOk();
        $this->assertEquals($child1->id, $resDefault->viewData('siswa')->id);
        $this->assertCount(2, $resDefault->viewData('allChildren'));

        // 2. Parent switches to child2 (alumni child)
        $resChild2 = $this->actingAs($parentUser)->get(route('dashboard', ['siswa_id' => $child2->id]));
        $resChild2->assertOk();
        $this->assertEquals($child2->id, $resChild2->viewData('siswa')->id);

        // 3. Parent attempts to access otherChild (should fallback to own child, zero leakage)
        $resHacker = $this->actingAs($parentUser)->get(route('dashboard', ['siswa_id' => $otherChild->id]));
        $resHacker->assertOk();
        $this->assertNotEquals($otherChild->id, $resHacker->viewData('siswa')->id);
        $this->assertEquals($child1->id, $resHacker->viewData('siswa')->id); // Safely defaulted to own child
    }
}
