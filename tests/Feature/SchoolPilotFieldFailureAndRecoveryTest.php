<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Guru;
use App\Models\HomeroomAssignment;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\StudentEnrollment;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\FaceBiometricService;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchoolPilotFieldFailureAndRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected TahunAjaran $ta;
    protected Jurusan $jurusanPplg;
    protected Jurusan $jurusanAnimasi;
    protected Angkatan $angkatanPplg;
    protected Kelas $kelasPplg;
    protected Kelas $kelasAnimasi;
    protected Room $roomPplg;
    protected Room $roomAnimasi;
    protected User $studentUser;
    protected Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Geofence (SMK Negeri 1 Ciomas)
        LokasiSekolah::create([
            'latitude'  => -6.602115,
            'longitude' => 106.760124,
            'radius'    => 100,
        ]);

        // 2. Academic Year
        $this->ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], [
            'tahun_mulai'     => 2026,
            'tahun_selesai'   => 2027,
            'semester_aktif'  => 'ganjil',
            'tanggal_mulai'   => '2026-07-15',
            'tanggal_selesai' => '2027-06-25',
            'is_active'       => true,
            'status'          => 'aktif',
        ]);
        $this->ta->update(['is_active' => true, 'status' => 'aktif', 'semester_aktif' => 'ganjil']);

        // 3. Departments
        $this->jurusanPplg = Jurusan::firstOrCreate(['kode_jurusan' => 'PPLG'], [
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active'    => true,
        ]);
        $this->jurusanAnimasi = Jurusan::firstOrCreate(['kode_jurusan' => 'ANIMASI'], [
            'nama_jurusan' => 'Animasi',
            'is_active'    => true,
        ]);

        // 4. Cohort & Classes
        $this->angkatanPplg = Angkatan::firstOrCreate(['tahun_masuk' => 2026, 'jurusan_id' => $this->jurusanPplg->id], [
            'nomor_angkatan'  => 19,
            'nama_angkatan'   => 'Angkatan 19 PPLG',
            'kode_angkatan'   => 'AKT-PPLG-19',
            'tahun_ajaran'    => '2026/2027',
            'tahun_kelulusan' => 2029,
            'status'          => 'aktif',
            'is_active'       => true,
        ]);

        $this->kelasPplg = Kelas::firstOrCreate(['nama_kelas' => 'X PPLG 1', 'tahun_ajaran_id' => $this->ta->id], [
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanPplg->id,
            'angkatan_id'     => $this->angkatanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 12,
            'is_active'       => true,
        ]);

        $this->kelasAnimasi = Kelas::firstOrCreate(['nama_kelas' => 'X ANIMASI 1', 'tahun_ajaran_id' => $this->ta->id], [
            'tingkat'         => 10,
            'nomor_rombel'    => 1,
            'jurusan_id'      => $this->jurusanAnimasi->id,
            'angkatan_id'     => $this->angkatanPplg->id,
            'tahun_ajaran'    => '2026/2027',
            'jumlah_siswa'    => 10,
            'is_active'       => true,
        ]);

        // 5. Rooms & Assignments
        $this->roomPplg = Room::firstOrCreate(['code' => 'LAB-PPLG-01'], [
            'name'         => 'Laboratorium Komputer PPLG 1',
            'building'     => 'Gedung B',
            'capacity'     => 36,
            'type'         => 'lab',
            'qr_token'     => 'QR-PPLG-VALID-TOKEN',
            'qr_is_active' => true,
            'is_active'    => true,
        ]);
        RoomAssignment::firstOrCreate([
            'room_id'      => $this->roomPplg->id,
            'kelas_id'     => $this->kelasPplg->id,
            'tahun_ajaran' => '2026/2027',
        ], [
            'day_of_week' => null,
            'start_time'  => null,
            'end_time'    => null,
            'starts_on'   => '2026-07-15',
            'ends_on'     => '2027-06-25',
            'status'      => 'active',
        ]);

        $this->roomAnimasi = Room::firstOrCreate(['code' => 'STUDIO-ANIM-01'], [
            'name'         => 'Studio Animasi 1',
            'building'     => 'Gedung C',
            'capacity'     => 36,
            'type'         => 'lab',
            'qr_token'     => 'QR-ANIMASI-VALID-TOKEN',
            'qr_is_active' => true,
            'is_active'    => true,
        ]);
        RoomAssignment::firstOrCreate([
            'room_id'      => $this->roomAnimasi->id,
            'kelas_id'     => $this->kelasAnimasi->id,
            'tahun_ajaran' => '2026/2027',
        ], [
            'day_of_week' => null,
            'start_time'  => null,
            'end_time'    => null,
            'starts_on'   => '2026-07-15',
            'ends_on'     => '2027-06-25',
            'status'      => 'active',
        ]);

        // 6. Student
        $this->studentUser = User::create([
            'name'                 => 'Aditya Pratama',
            'email'                => '262710001@myskanic.com',
            'role'                 => 'siswa',
            'jurusan_id'           => $this->jurusanPplg->id,
            'password'             => Hash::make('password123'),
            'must_change_password' => false,
        ]);

        $this->siswa = Siswa::create([
            'user_id'               => $this->studentUser->id,
            'nis'                   => '262710001',
            'nisn'                  => '0089100001',
            'nama'                  => 'Aditya Pratama',
            'jenis_kelamin'         => 'L',
            'kelas_id'              => $this->kelasPplg->id,
            'angkatan_id'           => $this->angkatanPplg->id,
            'tahun_masuk'           => 2026,
            'tahun_ajaran_masuk_id' => $this->ta->id,
            'status'                => 'aktif',
            'biometric_status'      => 'enrolled',
        ]);

        StudentEnrollment::create([
            'siswa_id'                  => $this->siswa->id,
            'tahun_ajaran_id'           => $this->ta->id,
            'tahun_ajaran'              => '2026/2027',
            'kelas_id'                  => $this->kelasPplg->id,
            'jurusan_id'                => $this->jurusanPplg->id,
            'angkatan_id'               => $this->angkatanPplg->id,
            'tingkat'                   => 10,
            'is_active'                 => true,
            'status_enrollment'         => 'aktif',
            'status_verifikasi_histori' => 'terverifikasi',
        ]);

        SiswaFaceBiometric::create([
            'siswa_id'          => $this->siswa->id,
            'face_descriptor'   => json_encode(array_fill(0, 128, 0.05)),
            'sample_photo'      => 'biometrics/pilot_262710001.jpg',
            'enrollment_status' => 'enrolled',
            'registered_at'     => now(),
            'enrolled_at'       => now(),
            'is_active'         => true,
            'quality_score'     => 98.5,
        ]);
    }

    protected function getDummyValidPayload(string $qrToken, float $lat = -6.602115, float $lng = 106.760124): array
    {
        return [
            'tipe'            => 'masuk',
            'qr_token'        => $qrToken,
            'latitude'        => $lat,
            'longitude'       => $lng,
            'foto'            => 'data:image/jpeg;base64,' . base64_encode('fake-selfie-binary-content'),
            'face_descriptor' => array_fill(0, 128, 0.05),
            'challenge_token' => 'dummy_token',
            'liveness_proof'  => [
                'completed_challenges' => ['front_facing'],
            ],
        ];
    }

    public function test_qr_code_from_different_class_is_rejected(): void
    {
        // Student in X PPLG 1 tries to scan QR of X Animasi 1
        $payload = $this->getDummyValidPayload($this->roomAnimasi->qr_token);

        $res = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);

        $res->assertStatus(422);
        $res->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('bukan', $res->json('message'));
    }

    public function test_unknown_or_rotated_qr_code_is_rejected(): void
    {
        $payload = $this->getDummyValidPayload('UNKNOWN-ROTATED-TOKEN-999');

        $res = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);

        $res->assertStatus(404);
        $res->assertJsonFragment(['success' => false]);
    }

    public function test_out_of_radius_gps_is_rejected(): void
    {
        // 500 meters away from school
        $payload = $this->getDummyValidPayload($this->roomPplg->qr_token, -6.606000, 106.765000);

        $res = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);

        $res->assertStatus(400);
        $res->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('di luar radius sekolah', $res->json('message'));
    }

    public function test_duplicate_attendance_submission_is_prevented(): void
    {
        // Mock FaceBiometricService to return valid match
        $biometricServiceMock = \Mockery::mock(FaceBiometricService::class);
        $biometricServiceMock->shouldReceive('verify')->andReturn([
            'success'        => true,
            'confidence'     => 98.0,
            'liveness_score' => 99.0,
        ]);
        $this->app->instance(FaceBiometricService::class, $biometricServiceMock);

        $payload = $this->getDummyValidPayload($this->roomPplg->qr_token);

        // 1. First check-in
        $res1 = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);
        $res1->assertOk();
        $res1->assertJsonFragment(['success' => true]);

        // 2. Second check-in on same day
        $res2 = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);
        $res2->assertStatus(400);
        $res2->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('sudah melakukan absen masuk hari ini', $res2->json('message'));

        // Assert exactly 1 record in database
        $this->assertEquals(1, \App\Models\Absensi::where('siswa_id', $this->siswa->id)->count());
    }

    public function test_whatsapp_failure_does_not_abort_attendance_recording(): void
    {
        // Mock FaceBiometricService to succeed
        $biometricServiceMock = \Mockery::mock(FaceBiometricService::class);
        $biometricServiceMock->shouldReceive('verify')->andReturn([
            'success'        => true,
            'confidence'     => 95.0,
            'liveness_score' => 96.0,
        ]);
        $this->app->instance(FaceBiometricService::class, $biometricServiceMock);

        // Mock WhatsAppNotificationService to simulate gateway connection error
        $waMock = \Mockery::mock(WhatsAppNotificationService::class);
        $waMock->shouldReceive('sendAttendanceNotification')->andReturn([
            'success' => false,
            'error'   => 'Fonnte Gateway timeout',
        ]);
        $this->app->instance(WhatsAppNotificationService::class, $waMock);

        $payload = $this->getDummyValidPayload($this->roomPplg->qr_token);

        $res = $this->actingAs($this->studentUser)->postJson(route('absensi.store'), $payload);

        // Attendance must still succeed and be recorded
        $res->assertOk();
        $res->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('absensis', [
            'siswa_id' => $this->siswa->id,
            'status'   => 'Hadir',
        ]);
    }
}
