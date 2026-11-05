<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarDay;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Izin;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests for the three features added after the audit:
 *   1. School settings (jam masuk / jam pulang) replacing hardcoded values.
 *   2. Academic calendar non-effective days excluding alpa.
 *   3. Leave-request approval workflow (pending -> approved/rejected).
 */
class SchoolSettingsCalendarAndIzinTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $teacher;
    protected User $studentUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.settings.test@skanic.com'],
            ['name' => 'Admin Settings Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'SETT'],
            ['nama_jurusan' => 'Settings Test Major', 'is_active' => true]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => '10 SETT 1'],
            ['jurusan_id' => $this->jurusan->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-sett-1', 'jumlah_siswa' => 30]
        );

        $this->teacher = User::firstOrCreate(
            ['email' => 'guru.settings.test@skanic.com'],
            ['name' => 'Guru Settings Test', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $guru = Guru::firstOrCreate(
            ['user_id' => $this->teacher->id],
            ['nama' => 'Guru Settings Test', 'jenis_kelamin' => 'L', 'is_active' => true, 'mapel' => 'Test']
        );

        \App\Models\HomeroomAssignment::create([
            'guru_id'      => $guru->id,
            'kelas_id'     => $this->kelas->id,
            'tahun_ajaran' => '2026/2027',
            'starts_on'    => now()->toDateString(),
            'is_active'    => true,
        ]);

        $this->studentUser = User::firstOrCreate(
            ['email' => 'siswa.settings.test@skanic.com'],
            ['name' => 'Siswa Settings Test', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );

        $this->siswa = Siswa::firstOrCreate(
            ['user_id' => $this->studentUser->id],
            ['nis' => 'SETT-001', 'nisn' => 'SETT-N1', 'nama' => 'Siswa Settings Test', 'jenis_kelamin' => 'L', 'kelas_id' => $this->kelas->id, 'biometric_status' => 'enrolled']
        );

        // The biometric.enrolled middleware requires an actual enrolled
        // template row, not just the status flag.
        \App\Models\SiswaFaceBiometric::updateOrCreate(
            ['siswa_id' => $this->siswa->id],
            [
                'face_descriptor'   => array_fill(0, 128, 0.5),
                'quality_score'     => 95.0,
                'registered_at'     => now(),
                'is_active'         => true,
                'enrollment_status' => 'enrolled',
            ]
        );
    }

    // ---------- 1. School settings ----------

    public function test_admin_can_view_and_update_school_settings()
    {
        $this->actingAs($this->admin)->get(route('settings.index'))->assertStatus(200);

        $this->actingAs($this->admin)->post(route('settings.update'), [
            'jam_masuk'    => '06:45',
            'jam_pulang'   => '15:30',
            'nama_sekolah' => 'SMK Test Negeri',
        ])->assertRedirect();

        $this->assertSame('06:45', SchoolSetting::value('jam_masuk'));
        $this->assertSame('15:30', SchoolSetting::value('jam_pulang'));
    }

    public function test_settings_reject_departure_before_arrival()
    {
        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'jam_masuk'  => '08:00',
            'jam_pulang' => '07:00',
        ]);

        $response->assertSessionHasErrors('jam_pulang');
    }

    public function test_non_admin_cannot_access_settings()
    {
        $this->actingAs($this->teacher)->get(route('settings.index'))->assertStatus(302);
    }

    // ---------- 2. Academic calendar ----------

    public function test_admin_can_add_and_remove_non_effective_day()
    {
        $this->actingAs($this->admin)->post(route('academic_calendar.store'), [
            'tanggal'       => '2026-11-20',
            'nama_kegiatan' => 'Libur Nasional Test',
        ])->assertRedirect();

        $this->assertTrue(AcademicCalendarDay::isNonEffective('2026-11-20'));

        $day = AcademicCalendarDay::whereDate('tanggal', '2026-11-20')->first();
        $this->actingAs($this->admin)
            ->post(route('academic_calendar.destroy', $day->id))
            ->assertRedirect();

        $this->assertFalse(AcademicCalendarDay::isNonEffective('2026-11-20'));
    }

    public function test_non_effective_day_is_excluded_from_effective_day_count()
    {
        // A Monday marked as a holiday must not count as an effective day.
        AcademicCalendarDay::create([
            'tanggal' => '2026-11-09', // Monday
            'nama_kegiatan' => 'Libur Test',
            'is_active' => true,
        ]);

        $controller = new \App\Http\Controllers\AbsensiController(
            app(\App\Services\FaceBiometricService::class),
            app(\App\Services\WhatsAppNotificationService::class)
        );

        $method = new \ReflectionMethod($controller, 'countEffectiveDays');
        $method->setAccessible(true);

        $withHoliday = $method->invoke(
            $controller,
            \Carbon\Carbon::parse('2026-11-09'),
            \Carbon\Carbon::parse('2026-11-09')
        );

        $this->assertSame(0, $withHoliday, 'A registered non-effective day must not count as effective.');
    }

    // ---------- 3. Izin approval workflow ----------

    public function test_student_submission_creates_pending_request_not_attendance()
    {
        $this->actingAs($this->studentUser)->post(route('izin.store'), [
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'sakit',
            'keterangan' => 'Demam tinggi',
        ])->assertRedirect();

        $izin = Izin::where('siswa_id', $this->siswa->id)->first();
        $this->assertNotNull($izin);
        $this->assertSame('pending', $izin->status);

        // Critically: no attendance row yet — it is only written on approval.
        $this->assertDatabaseMissing('absensis', [
            'siswa_id' => $this->siswa->id,
            'tanggal'  => now()->addDay()->toDateString(),
        ]);
    }

    public function test_homeroom_teacher_can_approve_and_attendance_is_written()
    {
        $izin = Izin::create([
            'siswa_id'   => $this->siswa->id,
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Acara keluarga',
            'status'     => 'pending',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('izin.approve', $izin->id), ['catatan' => 'Disetujui wali kelas'])
            ->assertRedirect();

        $izin->refresh();
        $this->assertSame('approved', $izin->status);
        $this->assertSame($this->teacher->id, $izin->decided_by);

        // Attendance is created with the leave type as status.
        $this->assertDatabaseHas('absensis', [
            'siswa_id' => $this->siswa->id,
            'status'   => 'izin',
        ]);
    }

    public function test_rejection_does_not_write_attendance()
    {
        $izin = Izin::create([
            'siswa_id'   => $this->siswa->id,
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Alasan tidak jelas',
            'status'     => 'pending',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('izin.reject', $izin->id), ['catatan' => 'Ditolak'])
            ->assertRedirect();

        $this->assertSame('rejected', $izin->fresh()->status);
        $this->assertDatabaseMissing('absensis', ['siswa_id' => $this->siswa->id]);
    }

    public function test_teacher_cannot_approve_request_from_another_class()
    {
        $otherJurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'OTH'],
            ['nama_jurusan' => 'Other Major', 'is_active' => true]
        );
        $otherKelas = Kelas::firstOrCreate(
            ['nama_kelas' => '10 OTH 1'],
            ['jurusan_id' => $otherJurusan->id, 'tingkat' => '10', 'nomor_rombel' => '1', 'tahun_ajaran' => '2026/2027', 'qr_token' => 'qr-oth-1', 'jumlah_siswa' => 30]
        );
        $otherUser = User::firstOrCreate(
            ['email' => 'siswa.other.izin@skanic.com'],
            ['name' => 'Siswa Other', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );
        $otherSiswa = Siswa::firstOrCreate(
            ['user_id' => $otherUser->id],
            ['nis' => 'OTH-001', 'nisn' => 'OTH-N1', 'nama' => 'Siswa Other', 'jenis_kelamin' => 'P', 'kelas_id' => $otherKelas->id, 'biometric_status' => 'enrolled']
        );

        $izin = Izin::create([
            'siswa_id'   => $otherSiswa->id,
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Bukan kelas saya',
            'status'     => 'pending',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('izin.approve', $izin->id))
            ->assertStatus(403);

        $this->assertSame('pending', $izin->fresh()->status);
    }

    public function test_student_can_cancel_pending_request()
    {
        $izin = Izin::create([
            'siswa_id'   => $this->siswa->id,
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Batal',
            'status'     => 'pending',
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('izin.cancel', $izin->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('izins', ['id' => $izin->id]);
    }

    public function test_approved_request_cannot_be_cancelled_by_student()
    {
        $izin = Izin::create([
            'siswa_id'   => $this->siswa->id,
            'tanggal'    => now()->addDay()->toDateString(),
            'jenis'      => 'izin',
            'keterangan' => 'Sudah disetujui',
            'status'     => 'approved',
            'decided_by' => $this->teacher->id,
            'decided_at' => now(),
        ]);

        $this->actingAs($this->studentUser)
            ->post(route('izin.cancel', $izin->id))
            ->assertRedirect();

        $this->assertDatabaseHas('izins', ['id' => $izin->id]);
    }
}