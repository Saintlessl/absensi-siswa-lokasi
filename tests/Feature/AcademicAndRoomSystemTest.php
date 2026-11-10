<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Siswa;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AcademicAndRoomSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;
    protected User $kaprogPplg;
    protected Jurusan $pplg;
    protected Kelas $kelas10;
    protected Room $room1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::firstOrCreate(
            ['email' => 'test.superadmin@skanic.com'],
            ['name' => 'Super Admin Test', 'password' => bcrypt('password123'), 'role' => 'superadmin']
        );

        $this->pplg = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim', 'is_active' => true]
        );

        $this->kaprogPplg = User::firstOrCreate(
            ['email' => 'test.kaprog@skanic.com'],
            ['name' => 'Kaprog PPLG Test', 'password' => bcrypt('password123'), 'role' => 'kaprog', 'jurusan_id' => $this->pplg->id]
        );

        $this->kelas10 = Kelas::firstOrCreate(
            ['nama_kelas' => '10 PPLG 1'],
            ['jurusan_id' => $this->pplg->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'token-k10', 'jumlah_siswa' => 30]
        );

        $this->room1 = Room::firstOrCreate(
            ['code' => 'TEST-R101'],
            ['name' => 'Ruang Test 101', 'capacity' => 36, 'building' => 'Gedung A', 'is_active' => true, 'qr_token' => 'qr-token-test-101']
        );
    }

    public function test_superadmin_can_access_academic_structure()
    {
        $response = $this->actingAs($this->superadmin)->get(route('academic.index'));
        $response->assertStatus(200);
        $response->assertSee('Struktur Akademik');
    }

    public function test_superadmin_can_access_room_management()
    {
        $response = $this->actingAs($this->superadmin)->get(route('rooms.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Ruangan Fisik');
    }

    public function test_superadmin_can_access_room_qr_codes()
    {
        $response = $this->actingAs($this->superadmin)->get(route('absensi.qrcodes'));
        $response->assertStatus(200);
        $response->assertSee('QR Code Presensi Fisik Ruangan');
    }

    public function test_superadmin_can_access_rekap_absensi()
    {
        $response = $this->actingAs($this->superadmin)->get(route('rekapabsensi'));
        $response->assertStatus(200);
        $response->assertSee('Rekap Absensi');

        $responseRekapKelas = $this->actingAs($this->superadmin)->get(route('rekapkelas'));
        $responseRekapKelas->assertStatus(200);
    }

    public function test_superadmin_can_access_data_siswa()
    {
        $response = $this->actingAs($this->superadmin)->get(route('siswa'));
        $response->assertStatus(200);
        $response->assertSee('Data Siswa');
    }

    public function test_kaprog_can_access_data_siswa_scoped()
    {
        $response = $this->actingAs($this->kaprogPplg)->get(route('siswa'));
        $response->assertStatus(200);
        $response->assertSee('Data Siswa');
    }

    public function test_room_allocation_conflict_detection_prevents_overlap()
    {
        $service = app(RoomAssignmentService::class);

        // First assignment
        $service->create([
            'room_id' => $this->room1->id,
            'kelas_id' => $this->kelas10->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on' => '2026-11-01',
            'ends_on' => '2026-11-30',
            'status' => 'active',
            'created_by' => $this->superadmin->id,
        ]);

        // Attempt overlapping assignment on same room with another class
        $kelas2 = Kelas::firstOrCreate(
            ['nama_kelas' => '10 PPLG 2'],
            ['jurusan_id' => $this->pplg->id, 'tingkat' => '10', 'nomor_rombel' => '2', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'token-k10-2', 'jumlah_siswa' => 30]
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->create([
            'room_id' => $this->room1->id,
            'kelas_id' => $kelas2->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on' => '2026-11-15',
            'ends_on' => '2026-12-15',
            'status' => 'active',
            'created_by' => $this->superadmin->id,
        ]);
    }

    public function test_kaprog_cannot_allocate_room_to_another_major_class()
    {
        $animasi = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'ANIMASI'],
            ['nama_jurusan' => 'Animasi', 'is_active' => true]
        );

        $kelasAnimasi = Kelas::firstOrCreate(
            ['nama_kelas' => '10 ANIMASI 1'],
            ['jurusan_id' => $animasi->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'token-ani1', 'jumlah_siswa' => 30]
        );

        // Kaprog PPLG tries to allocate room to Animasi class
        $response = $this->actingAs($this->kaprogPplg)->post(route('rooms.assignments.store'), [
            'room_id' => $this->room1->id,
            'kelas_id' => $kelasAnimasi->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on' => '2026-11-01',
            'ends_on' => '2026-11-30',
        ]);

        $response->assertStatus(403);
    }

    public function test_room_qr_resolves_assigned_rombel_and_validates_attendance()
    {
        // Allocate room1 to kelas10 for today
        $service = app(RoomAssignmentService::class);
        $service->create([
            'room_id' => $this->room1->id,
            'kelas_id' => $this->kelas10->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on' => Carbon::today()->subDays(1)->toDateString(),
            'ends_on' => Carbon::today()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->superadmin->id,
        ]);

        // Student from kelas10
        $userSiswa = User::firstOrCreate(
            ['email' => 'siswa.test10@skanic.com'],
            ['name' => 'Siswa Test 10', 'password' => bcrypt('password123'), 'role' => 'siswa']
        );

        $siswa = Siswa::firstOrCreate(
            ['nis' => '9999901'],
            [
                'user_id' => $userSiswa->id,
                'nama' => 'Siswa Test 10',
                'kelas_id' => $this->kelas10->id,
                'jenis_kelamin' => 'L',
                'biometric_status' => 'enrolled',
            ]
        );
        $siswa->update(['biometric_status' => 'enrolled']);

        \App\Models\SiswaFaceBiometric::create([
            'siswa_id' => $siswa->id,
            'face_descriptor' => array_fill(0, 128, 0.05),
            'face_descriptors_samples' => [array_fill(0, 128, 0.05)],
            'enrollment_status' => 'enrolled',
            'is_active' => true,
        ]);

        // Validate QR using the room's qr_token
        $response = $this->actingAs($userSiswa)->postJson(route('absensi.validateqr'), [
            'qr_token' => $this->room1->qr_token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonPath('kelas.nama_kelas', '10 PPLG 1');
    }
}
