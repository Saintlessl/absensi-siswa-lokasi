<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\SiswaFaceBiometric;
use App\Models\User;
use App\Services\FaceBiometricService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression tests for attendance integrity.
 *
 * Covers BUG-002 / BUG-003: attendance must never be recorded without a
 * successful face verification, and is_verified_biometric must reflect the
 * actual verification outcome instead of being hardcoded to true.
 */
class AttendanceIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;
    protected User $studentUser;
    protected Siswa $siswa;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::firstOrCreate(
            ['email' => 'admin.attendance.test@skanic.com'],
            ['name' => 'Admin Attendance Test', 'password' => bcrypt('password123'), 'role' => 'superadmin', 'status' => 'active']
        );

        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'ATT_TEST'],
            ['nama_jurusan' => 'Attendance Test Major', 'is_active' => true]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => '10 ATT Test 1'],
            [
                'jurusan_id' => $jurusan->id,
                'tingkat' => '10',
                'nomor_rombel' => '1',
                'tahun_ajaran' => '2026/2027',
                'qr_token' => 'qr-att-test-k10',
                'jumlah_siswa' => 30,
            ]
        );

        $this->studentUser = User::firstOrCreate(
            ['email' => 'siswa.attendance.test@skanic.com'],
            ['name' => 'Siswa Attendance Test', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );

        $this->siswa = Siswa::firstOrCreate(
            ['user_id' => $this->studentUser->id],
            [
                'nis' => 'ATT-TEST-001',
                'nisn' => 'ATT-TEST-NISN-001',
                'nama' => 'Siswa Attendance Test',
                'jenis_kelamin' => 'L',
                'kelas_id' => $this->kelas->id,
                'biometric_status' => 'enrolled',
            ]
        );

        // Enrolled biometric template so verification can actually run.
        $template = array_fill(0, 128, 0.5);
        SiswaFaceBiometric::updateOrCreate(
            ['siswa_id' => $this->siswa->id],
            [
                'face_descriptor'   => $template,
                'quality_score'     => 95.0,
                'registered_at'     => now(),
                'is_active'         => true,
                'enrollment_status' => 'enrolled',
            ]
        );

        // Geofence that contains the coordinate used by the tests.
        LokasiSekolah::firstOrCreate(
            ['id' => 1],
            ['latitude' => -0.5, 'longitude' => 123.0, 'radius' => 500]
        );
    }

    public function test_attendance_is_rejected_without_face_descriptor()
    {
        $response = $this->actingAs($this->studentUser)
            ->postJson(route('absensi.store'), [
                'qr_token'  => $this->kelas->qr_token,
                'foto'      => 'data:image/jpeg;base64,' . base64_encode('dummy'),
                'latitude'  => -0.5,
                'longitude' => 123.0,
                'tipe'      => 'masuk',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['face_descriptor', 'challenge_token', 'liveness_proof']);

        $this->assertDatabaseMissing('absensis', ['siswa_id' => $this->siswa->id]);
    }

    public function test_attendance_is_rejected_when_face_verification_fails()
    {
        // An unissued challenge token must fail anti-replay and therefore reject attendance.
        $response = $this->actingAs($this->studentUser)
            ->postJson(route('absensi.store'), [
                'qr_token'        => $this->kelas->qr_token,
                'foto'            => 'data:image/jpeg;base64,' . base64_encode('dummy'),
                'latitude'        => -0.5,
                'longitude'       => 123.0,
                'tipe'            => 'masuk',
                'face_descriptor' => array_fill(0, 128, 0.01),
                'challenge_token' => 'chl_tidak_pernah_diterbitkan',
                'liveness_proof'  => [
                    'liveness_score'        => 90.0,
                    'completed_challenges'  => [['type' => 'front_facing', 'duration_ms' => 500]],
                ],
            ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertDatabaseMissing('absensis', ['siswa_id' => $this->siswa->id]);
    }

    public function test_attendance_requires_face_descriptor_to_be_128_dimensions()
    {
        $response = $this->actingAs($this->studentUser)
            ->postJson(route('absensi.store'), [
                'qr_token'        => $this->kelas->qr_token,
                'foto'            => 'data:image/jpeg;base64,' . base64_encode('dummy'),
                'latitude'        => -0.5,
                'longitude'       => 123.0,
                'tipe'            => 'masuk',
                'face_descriptor' => [0.1, 0.2, 0.3],
                'challenge_token' => 'any',
                'liveness_proof'  => [
                    'completed_challenges' => [['type' => 'front_facing', 'duration_ms' => 500]],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['face_descriptor']);
        $this->assertDatabaseMissing('absensis', ['siswa_id' => $this->siswa->id]);
    }

    public function test_successful_verification_records_attendance_with_real_confidence()
    {
        $template = $this->siswa->faceBiometric()->first()->face_descriptor;

        // Issue a real attendance challenge through the service.
        $challenge = app(FaceBiometricService::class)->generateAttendanceChallenge($this->siswa);

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('absensi.store'), [
                'qr_token'        => $this->kelas->qr_token,
                'foto'            => 'data:image/jpeg;base64,' . base64_encode('dummy'),
                'latitude'        => -0.5,
                'longitude'       => 123.0,
                'tipe'            => 'masuk',
                'face_descriptor' => $template,
                'challenge_token' => $challenge['token'],
                'liveness_proof'  => [
                    'liveness_score'       => 97.0,
                    'completed_challenges' => [['type' => 'front_facing', 'duration_ms' => 500]],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('absensis', [
            'siswa_id'              => $this->siswa->id,
            'is_verified_biometric' => true,
        ]);

        // Confidence must come from the verification result, not a hardcoded 98.0
        $record = \App\Models\Absensi::where('siswa_id', $this->siswa->id)->first();
        $this->assertNotNull($record->confidence_masuk);
        $this->assertGreaterThan(0, $record->confidence_masuk);
    }

    public function test_teacher_cannot_mark_attendance_for_student_outside_their_classes()
    {
        // Regression for BUG-004: updateHarian() accepted any siswa_id.
        $teacherUser = User::firstOrCreate(
            ['email' => 'guru.scope.attendance@skanic.com'],
            ['name' => 'Guru Scope Test', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        Guru::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['nama' => 'Guru Scope Test', 'jenis_kelamin' => 'L', 'is_active' => true, 'mapel' => 'Test']
        );

        // Teacher teaches nothing, so they must not be able to write attendance.
        $response = $this->actingAs($teacherUser)
            ->post(route('absensi.harian.update'), [
                'siswa_id'   => $this->siswa->id,
                'status'     => 'Hadir',
                'keterangan' => 'Manipulasi',
            ]);

        $this->assertContains($response->getStatusCode(), [403, 302]);
        $this->assertDatabaseMissing('absensis', [
            'siswa_id'   => $this->siswa->id,
            'keterangan' => 'Manipulasi',
        ]);
    }
}