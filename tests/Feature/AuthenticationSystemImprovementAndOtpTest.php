<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\StudentPasswordResetOtp;
use App\Models\User;
use App\Services\WhatsAppOtpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationSystemImprovementAndOtpTest extends TestCase
{
    use RefreshDatabase;

    private User $siswaUser;
    private Siswa $siswa;
    private User $guruUser;
    private Guru $guru;
    private User $kaprogUser;
    private Guru $kaprog;
    private User $ortuUser;
    private OrangTua $ortu;
    private User $adminUser;
    private User $superAdminUser;
    private Jurusan $jurusan;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Jurusan & Kelas
        $this->jurusan = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active'    => true,
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas'   => 'XII PPLG 1',
            'jurusan_id'   => $this->jurusan->id,
            'tingkat'      => 12,
            'tahun_ajaran' => '2026/2027',
            'is_active'    => true,
        ]);

        // 2. Siswa (exempt from face verification for direct login test assertion)
        $this->siswaUser = User::create([
            'name'     => 'Ahmad Siswa',
            'email'    => 'ahmad.siswa@skanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'siswa',
            'status'   => 'active',
        ]);
        $this->siswa = Siswa::create([
            'user_id'             => $this->siswaUser->id,
            'nis'                 => '12345',
            'nisn'                => '0012345678',
            'nama'                => 'Ahmad Siswa',
            'kelas_id'            => $this->kelas->id,
            'jurusan_id'          => $this->jurusan->id,
            'jenis_kelamin'       => 'L',
            'no_telp_siswa'       => '081234567890',
            'status'              => 'aktif',
            'is_biometric_exempt' => true,
        ]);

        // 3. Guru
        $this->guruUser = User::create([
            'name'     => 'Budi Guru',
            'email'    => 'budi.guru@skanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'guru',
            'status'   => 'active',
        ]);
        $this->guru = Guru::create([
            'user_id'       => $this->guruUser->id,
            'nip'           => '198501012010011001',
            'nama'          => 'Budi Guru',
            'jenis_kelamin' => 'L',
            'guru_type'     => 'biasa',
        ]);

        // 4. Kaprog
        $this->kaprogUser = User::create([
            'name'       => 'Siti Kaprog',
            'email'      => 'siti.kaprog@skanic.test',
            'password'   => bcrypt('password123'),
            'role'       => 'kaprog',
            'status'     => 'active',
            'jurusan_id' => $this->jurusan->id,
        ]);
        $this->kaprog = Guru::create([
            'user_id'       => $this->kaprogUser->id,
            'nip'           => '198602022011012002',
            'nama'          => 'Siti Kaprog',
            'jenis_kelamin' => 'P',
            'guru_type'     => 'kaprog',
        ]);

        // 5. Orang Tua
        $this->ortuUser = User::create([
            'name'     => 'Hasan Orang Tua',
            'email'    => 'hasan.ortu@skanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'orangtua',
            'status'   => 'active',
        ]);
        $this->ortu = OrangTua::create([
            'user_id'       => $this->ortuUser->id,
            'nama'          => 'Hasan Orang Tua',
            'jenis_kelamin' => 'L',
            'telepon'       => '081399998888',
            'siswa_id'      => $this->siswa->id,
        ]);
        $this->ortu->siswa()->attach($this->siswa->id);

        // 6. Admin
        $this->adminUser = User::create([
            'name'     => 'Admin Sekolah',
            'email'    => 'admin@skanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // 7. Super Admin
        $this->superAdminUser = User::create([
            'name'     => 'Super Administrator',
            'email'    => 'superadmin@skanic.test',
            'password' => bcrypt('password123'),
            'role'     => 'superadmin',
            'status'   => 'active',
        ]);
    }

    // =========================================================================
    // A. LOGIN SISWA
    // =========================================================================
    public function test_login_siswa_melalui_portal_siswa_berhasil(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'ahmad.siswa@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->siswaUser);
    }

    public function test_login_siswa_melalui_portal_guru_ditolak(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'ahmad.siswa@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun ini tidak dapat digunakan melalui portal login guru. Silakan gunakan portal login siswa.');
        $this->assertGuest();
    }

    // =========================================================================
    // B. LOGIN GURU
    // =========================================================================
    public function test_login_guru_melalui_portal_guru_berhasil(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'budi.guru@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->guruUser);
    }

    public function test_login_guru_melalui_portal_siswa_ditolak(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'budi.guru@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun ini tidak dapat digunakan melalui portal login siswa. Silakan gunakan portal login guru.');
        $this->assertGuest();
    }

    // =========================================================================
    // C. LOGIN KAPROG
    // =========================================================================
    public function test_login_kaprog_melalui_portal_guru_berhasil(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'siti.kaprog@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->kaprogUser);
    }

    public function test_login_kaprog_melalui_portal_siswa_ditolak(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'siti.kaprog@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun ini tidak dapat digunakan melalui portal login siswa. Silakan gunakan portal login guru.');
        $this->assertGuest();
    }

    // =========================================================================
    // D. LOGIN ORANG TUA
    // =========================================================================
    public function test_login_orang_tua_melalui_portal_siswa_berhasil_dan_buka_dashboard_orangtua(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'hasan.ortu@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->ortuUser);

        // Verify dashboard access renders Orang Tua content
        $dashResponse = $this->actingAs($this->ortuUser)->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('Ahmad Siswa'); // Anak yang terhubung
    }

    public function test_login_orang_tua_melalui_portal_guru_ditolak(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'hasan.ortu@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun ini tidak dapat digunakan melalui portal login guru. Silakan gunakan portal login siswa.');
        $this->assertGuest();
    }

    // =========================================================================
    // E. LOGIN ADMIN
    // =========================================================================
    public function test_login_admin_melalui_portal_siswa_berhasil(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'admin@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_login_admin_melalui_portal_guru_berhasil(): void
    {
        $response = $this->post('/proseslogin', [
            'email'       => 'admin@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    // =========================================================================
    // F. LOGIN SUPER ADMIN
    // =========================================================================
    public function test_login_superadmin_melalui_kedua_portal_berhasil(): void
    {
        // Via Portal Siswa
        $resSiswa = $this->post('/proseslogin', [
            'email'       => 'superadmin@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'siswa',
        ]);
        $resSiswa->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->superAdminUser);

        auth()->logout();

        // Via Portal Guru
        $resGuru = $this->post('/proseslogin', [
            'email'       => 'superadmin@skanic.test',
            'password'    => 'password123',
            'portal_role' => 'guru',
        ]);
        $resGuru->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->superAdminUser);
    }

    // =========================================================================
    // G. FORGOT PASSWORD & OTP WORKFLOW
    // =========================================================================
    public function test_forgot_password_identification_by_email_and_nisn(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'id'     => 'msg_test_12345',
            ], 200),
        ]);

        config(['services.fonnte.token' => 'dummy_fonnte_token_xyz']);

        // 1. Identify by Email
        $responseEmail = $this->post(route('password.identify'), [
            'identifier' => 'ahmad.siswa@skanic.test',
        ]);

        $responseEmail->assertRedirect(route('password.otp'));
        $responseEmail->assertSessionHas('success');
        $this->assertDatabaseHas('student_password_reset_otps', [
            'user_id'  => $this->siswaUser->id,
            'siswa_id' => $this->siswa->id,
            'no_telp'  => '6281234567890',
        ]);

        // 2. Identify by NISN
        $responseNisn = $this->post(route('password.identify'), [
            'identifier' => '0012345678',
        ]);

        $responseNisn->assertRedirect(route('password.otp'));
        $responseNisn->assertSessionHas('success');
    }

    public function test_forgot_password_rejects_non_student_roles(): void
    {
        // Guru rejected
        $responseGuru = $this->post(route('password.identify'), [
            'identifier' => 'budi.guru@skanic.test',
        ]);
        $responseGuru->assertSessionHas('error');
        $this->assertDatabaseCount('student_password_reset_otps', 0);

        // Kaprog rejected
        $responseKaprog = $this->post(route('password.identify'), [
            'identifier' => 'siti.kaprog@skanic.test',
        ]);
        $responseKaprog->assertSessionHas('error');

        // Admin rejected
        $responseAdmin = $this->post(route('password.identify'), [
            'identifier' => 'admin@skanic.test',
        ]);
        $responseAdmin->assertSessionHas('error');
    }

    public function test_forgot_password_rejects_student_without_valid_phone(): void
    {
        $this->siswa->update(['no_telp_siswa' => null]);

        $response = $this->post(route('password.identify'), [
            'identifier' => 'ahmad.siswa@skanic.test',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Nomor telepon WhatsApp pribadi belum terdaftar', session('error'));
        $this->assertDatabaseCount('student_password_reset_otps', 0);
    }

    public function test_otp_verification_success_wrong_code_and_expired(): void
    {
        // Create an OTP record manually
        $plainOtp = '654321';
        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $this->siswaUser->id,
            'siswa_id'            => $this->siswa->id,
            'no_telp'             => '6281234567890',
            'otp_hash'            => Hash::make($plainOtp),
            'attempts'            => 0,
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(60),
        ]);

        // Setup session
        session([
            'password_reset_flow' => [
                'otp_id'       => $otpRecord->id,
                'user_id'      => $this->siswaUser->id,
                'siswa_id'     => $this->siswa->id,
                'masked_phone' => '0812******90',
                'expires_at'   => $otpRecord->expires_at->timestamp,
                'resend_at'    => $otpRecord->resend_available_at->timestamp,
            ]
        ]);

        // 1. Wrong OTP code -> rejected and attempts incremented
        $resWrong = $this->post(route('password.otp.verify'), [
            'otp' => '111111',
        ]);
        $resWrong->assertSessionHas('error');
        $this->assertSame(1, $otpRecord->fresh()->attempts);

        // 2. Correct OTP code -> accepted, verified_at and reset_token created
        $resCorrect = $this->post(route('password.otp.verify'), [
            'otp' => $plainOtp,
        ]);
        $resCorrect->assertRedirect(route('password.reset.form'));
        $resCorrect->assertSessionHas('success');

        $otpRecord->refresh();
        $this->assertNotNull($otpRecord->verified_at);
        $this->assertNotNull($otpRecord->reset_token);
    }

    public function test_otp_max_attempts_locks_out_verification(): void
    {
        $plainOtp = '654321';
        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $this->siswaUser->id,
            'siswa_id'            => $this->siswa->id,
            'no_telp'             => '6281234567890',
            'otp_hash'            => Hash::make($plainOtp),
            'attempts'            => 5, // Already reached 5
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(60),
        ]);

        session([
            'password_reset_flow' => [
                'otp_id'       => $otpRecord->id,
                'user_id'      => $this->siswaUser->id,
                'siswa_id'     => $this->siswa->id,
                'masked_phone' => '0812******90',
                'expires_at'   => $otpRecord->expires_at->timestamp,
                'resend_at'    => $otpRecord->resend_available_at->timestamp,
            ]
        ]);

        $res = $this->post(route('password.otp.verify'), [
            'otp' => $plainOtp,
        ]);

        $res->assertSessionHas('error');
        $this->assertStringContainsString('Batas maksimal 5 kali', session('error'));
    }

    public function test_otp_expired_code_is_rejected(): void
    {
        $plainOtp = '654321';
        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $this->siswaUser->id,
            'siswa_id'            => $this->siswa->id,
            'no_telp'             => '6281234567890',
            'otp_hash'            => Hash::make($plainOtp),
            'attempts'            => 0,
            'expires_at'          => now()->subMinute(), // Expired!
            'resend_available_at' => now()->addSeconds(60),
        ]);

        session([
            'password_reset_flow' => [
                'otp_id'       => $otpRecord->id,
                'user_id'      => $this->siswaUser->id,
                'siswa_id'     => $this->siswa->id,
                'masked_phone' => '0812******90',
                'expires_at'   => $otpRecord->expires_at->timestamp,
                'resend_at'    => $otpRecord->resend_available_at->timestamp,
            ]
        ]);

        $res = $this->post(route('password.otp.verify'), [
            'otp' => $plainOtp,
        ]);

        $res->assertSessionHas('error');
        $this->assertStringContainsString('kadaluarsa', session('error'));
    }

    public function test_resend_otp_enforces_60_seconds_cooldown(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);
        config(['services.fonnte.token' => 'dummy_token']);

        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $this->siswaUser->id,
            'siswa_id'            => $this->siswa->id,
            'no_telp'             => '6281234567890',
            'otp_hash'            => Hash::make('123456'),
            'attempts'            => 0,
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(45), // Still on cooldown!
        ]);

        session([
            'password_reset_flow' => [
                'otp_id'       => $otpRecord->id,
                'user_id'      => $this->siswaUser->id,
                'siswa_id'     => $this->siswa->id,
                'masked_phone' => '0812******90',
                'expires_at'   => $otpRecord->expires_at->timestamp,
                'resend_at'    => $otpRecord->resend_available_at->timestamp,
            ]
        ]);

        // Attempt resend while on cooldown -> rejected
        $resBlocked = $this->post(route('password.otp.resend'));
        $resBlocked->assertSessionHas('error');
        $this->assertStringContainsString('Mohon tunggu', session('error'));

        // Fast-forward cooldown to expired
        $otpRecord->update(['resend_available_at' => now()->subSecond()]);

        $resAllowed = $this->post(route('password.otp.resend'));
        $resAllowed->assertSessionHas('success');

        // Old record should be deleted and replaced with a fresh record
        $this->assertDatabaseMissing('student_password_reset_otps', ['id' => $otpRecord->id]);
        $this->assertDatabaseCount('student_password_reset_otps', 1);
    }

    // =========================================================================
    // H. PASSWORD RESET COMPLETION
    // =========================================================================
    public function test_student_can_set_new_password_and_login_successfully(): void
    {
        $resetToken = 'secret_reset_token_64_chars_test_example';
        $otpRecord = StudentPasswordResetOtp::create([
            'user_id'             => $this->siswaUser->id,
            'siswa_id'            => $this->siswa->id,
            'no_telp'             => '6281234567890',
            'otp_hash'            => Hash::make('654321'),
            'reset_token'         => $resetToken,
            'verified_at'         => now(),
            'expires_at'          => now()->addMinutes(5),
            'resend_available_at' => now()->addSeconds(60),
        ]);

        session([
            'password_reset_flow' => [
                'otp_id'      => $otpRecord->id,
                'user_id'     => $this->siswaUser->id,
                'siswa_id'    => $this->siswa->id,
                'reset_token' => $resetToken,
                'verified'    => true,
            ]
        ]);

        // 1. Submit New Password
        $response = $this->post(route('password.reset.store'), [
            'password'              => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Check database
        $this->siswaUser->refresh();
        $this->assertTrue(Hash::check('NewPassword2026!', $this->siswaUser->password));
        $this->assertFalse(Hash::check('password123', $this->siswaUser->password)); // Old password invalid!

        // OTP record marked as used
        $otpRecord->refresh();
        $this->assertNotNull($otpRecord->used_at);
        $this->assertNull($otpRecord->reset_token);

        // Session cleared
        $this->assertNull(session('password_reset_flow'));

        // 2. Test Login with NEW password
        $resLogin = $this->post('/proseslogin', [
            'email'       => 'ahmad.siswa@skanic.test',
            'password'    => 'NewPassword2026!',
            'portal_role' => 'siswa',
        ]);

        $resLogin->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->siswaUser);
    }
}
