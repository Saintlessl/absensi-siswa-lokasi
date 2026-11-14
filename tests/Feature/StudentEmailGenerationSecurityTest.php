<?php

namespace Tests\Feature;

use App\Http\Controllers\SiswaController;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentEmailGenerationSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test@myskanic.com'],
            [
                'name'                 => 'Admin Test',
                'password'             => Hash::make('password123'),
                'role'                 => 'admin',
                'status'               => 'active',
                'must_change_password' => false,
            ]
        );

        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            [
                'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                'singkatan'    => 'PPLG',
                'is_active'    => true,
            ]
        );

        $ta = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai'   => 2026,
                'tahun_selesai' => 2027,
                'semester'      => 'ganjil',
                'is_active'     => true,
            ]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'X PPLG 1'],
            [
                'jurusan_id'      => $jurusan->id,
                'tingkat'         => '10',
                'tahun_ajaran'    => '2026/2027',
                'tahun_ajaran_id' => $ta->id,
                'is_active'       => true,
            ]
        );
    }

    private function createStudentPayload(array $overrides = []): array
    {
        $uniqueNum = rand(100000, 999999);
        return array_merge([
            'nisn'                    => '00' . rand(10000000, 99999999),
            'nis'                     => '21' . $uniqueNum,
            'nama'                    => 'Leonardo De Luciano',
            'no_telp_siswa'           => '0812' . rand(10000000, 99999999),
            'jenis_kelamin'           => 'L',
            'kelas_id'                => $this->kelas->id,
            'photo'                   => UploadedFile::fake()->image('photo.jpg', 300, 300),
            'parent_name'             => 'Wali Siswa ' . $uniqueNum,
            'parent_jenis_kelamin'    => 'L',
            'parent_phone'            => '0813' . rand(10000000, 99999999),
            'parent_address'          => 'Jl. Raya Pajajaran No. ' . rand(1, 100),
            'confirm_parent_verified' => 'on',
        ], $overrides);
    }

    /**
     * 1. Auto-generate email based on Nama Lengkap.
     * Leonardo De Luciano -> leonardodeluciano@myskanic.com
     * Budi Santoso -> budisantoso@myskanic.com
     * Citra Melani -> citramelani@myskanic.com
     * Muhammad Rizky Pratama -> muhammadrizkypratama@myskanic.com
     */
    public function test_auto_generates_student_email_based_on_nama_lengkap(): void
    {
        // Check unit generator
        $this->assertEquals('leonardodeluciano', SiswaController::generateStudentUsername('Leonardo De Luciano'));
        $this->assertEquals('budisantoso', SiswaController::generateStudentUsername('Budi Santoso'));
        $this->assertEquals('citramelani', SiswaController::generateStudentUsername('Citra Melani'));
        $this->assertEquals('muhammadrizkypratama', SiswaController::generateStudentUsername('Muhammad Rizky Pratama'));
        $this->assertEquals('josemariaalvarez', SiswaController::generateStudentUsername('José María Álvarez'));

        // Test through form submission
        $payload = $this->createStudentPayload([
            'nama' => 'Leonardo De Luciano',
            'nis'  => '99887701',
        ]);

        $res = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $res->assertRedirect();
        $res->assertSessionHas('success');

        $siswa = Siswa::where('nis', '99887701')->first();
        $this->assertNotNull($siswa);
        $studentUser = User::find($siswa->user_id);
        $this->assertNotNull($studentUser);

        // MUST be leonardodeluciano@myskanic.com, NOT 99887701@myskanic.com!
        $this->assertEquals('leonardodeluciano@myskanic.com', $studentUser->email);
        $this->assertNotEquals('99887701@myskanic.com', $studentUser->email);
    }

    /**
     * 2. Manual edit of username by Admin.
     * Nama Lengkap: Leonardo De Luciano
     * Admin modifies username to: leonardodluciano
     * Result email: leonardodluciano@myskanic.com
     */
    public function test_admin_can_manually_customize_student_email_username(): void
    {
        $payload = $this->createStudentPayload([
            'nama'               => 'Leonardo De Luciano',
            'nis'                => '99887702',
            'username'           => 'leonardodluciano',
            'is_manual_username' => '1',
        ]);

        $res = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $res->assertRedirect();
        $res->assertSessionHas('success');

        $siswa = Siswa::where('nis', '99887702')->first();
        $this->assertNotNull($siswa);
        $studentUser = User::find($siswa->user_id);
        $this->assertEquals('leonardodluciano@myskanic.com', $studentUser->email);
    }

    /**
     * 3. Duplicate handling for auto-generated usernames.
     * muhammadrizky@myskanic.com
     * muhammadrizky2@myskanic.com
     * muhammadrizky3@myskanic.com
     */
    public function test_auto_resolves_duplicate_username_with_incrementing_number(): void
    {
        // Student 1
        $payload1 = $this->createStudentPayload([
            'nama' => 'Muhammad Rizky',
            'nis'  => '99887703',
        ]);
        $res1 = $this->actingAs($this->admin)->post(route('siswa.store'), $payload1);
        $res1->assertRedirect();

        $user1 = Siswa::where('nis', '99887703')->first()->user;
        $this->assertEquals('muhammadrizky@myskanic.com', $user1->email);

        // Student 2 with same name
        $payload2 = $this->createStudentPayload([
            'nama' => 'Muhammad Rizky',
            'nis'  => '99887704',
        ]);
        $res2 = $this->actingAs($this->admin)->post(route('siswa.store'), $payload2);
        $res2->assertRedirect();

        $user2 = Siswa::where('nis', '99887704')->first()->user;
        $this->assertEquals('muhammadrizky2@myskanic.com', $user2->email);

        // Student 3 with same name
        $payload3 = $this->createStudentPayload([
            'nama' => 'Muhammad Rizky',
            'nis'  => '99887705',
        ]);
        $res3 = $this->actingAs($this->admin)->post(route('siswa.store'), $payload3);
        $res3->assertRedirect();

        $user3 = Siswa::where('nis', '99887705')->first()->user;
        $this->assertEquals('muhammadrizky3@myskanic.com', $user3->email);
    }

    /**
     * 4. Manual duplicate username is rejected with validation error.
     */
    public function test_manual_duplicate_username_rejected_with_validation_error(): void
    {
        // Pre-create user with username "bambang"
        User::firstOrCreate(
            ['email' => 'bambang@myskanic.com'],
            ['name' => 'Bambang Existing', 'password' => Hash::make('password123'), 'role' => 'siswa']
        );

        $payload = $this->createStudentPayload([
            'nama'               => 'Bambang Pamungkas',
            'nis'                => '99887706',
            'username'           => 'bambang',
            'is_manual_username' => '1',
        ]);

        $res = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $res->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringContainsString("Username 'bambang' sudah digunakan oleh siswa lain (@myskanic.com)", $errors[0]);
    }

    /**
     * 5. Paste handling: pasting full email strips domain without double domain.
     */
    public function test_paste_full_email_strips_domain_preventing_double_domain(): void
    {
        $payload = $this->createStudentPayload([
            'nama'     => 'Citra Melani',
            'nis'      => '99887707',
            'username' => 'citramelani@myskanic.com',
        ]);

        $res = $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $res->assertRedirect();

        $user = Siswa::where('nis', '99887707')->first()->user;
        $this->assertEquals('citramelani@myskanic.com', $user->email);
        $this->assertStringNotContainsString('@myskanic.com@myskanic.com', $user->email);
    }

    /**
     * 6. Edit Siswa preserves email unless explicitly modified.
     */
    public function test_edit_siswa_preserves_existing_email_unless_explicitly_changed(): void
    {
        $payload = $this->createStudentPayload([
            'nama' => 'Budi Santoso',
            'nis'  => '99887708',
        ]);
        $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $siswa = Siswa::where('nis', '99887708')->first();
        $this->assertEquals('budisantoso@myskanic.com', $siswa->user->email);

        // Edit student: change name only, do NOT change username
        $editPayload = [
            'nis'            => $siswa->nis,
            'nisn'           => $siswa->nisn,
            'nama'           => 'Budi S. Santoso', // name changed
            'jenis_kelamin'  => 'L',
            'kelas_id'       => $this->kelas->id,
            'parent_name'    => 'Orang Tua Budi',
            'parent_phone'   => '08123456789',
            'parent_address' => 'Alamat Budi',
            'username'       => 'budisantoso', // kept same
        ];

        $resEdit = $this->actingAs($this->admin)->post(route('siswa.update', ['id' => $siswa->id]), $editPayload);
        $resEdit->assertRedirect();

        $siswa->refresh();
        $this->assertEquals('Budi S. Santoso', $siswa->nama);
        $this->assertEquals('budisantoso@myskanic.com', $siswa->user->email);

        // Now explicitly change username in edit
        $editPayload['username'] = 'budisantosobaru';
        $resEdit2 = $this->actingAs($this->admin)->post(route('siswa.update', ['id' => $siswa->id]), $editPayload);
        $resEdit2->assertRedirect();

        $siswa->refresh();
        $this->assertEquals('budisantosobaru@myskanic.com', $siswa->user->email);
    }

    /**
     * 7. Login authentication with new student email and username.
     */
    public function test_student_can_login_with_new_email_format_and_username_only(): void
    {
        $payload = $this->createStudentPayload([
            'nama'     => 'Fajar Nugraha',
            'nis'      => '99887709',
            'password' => 'Password123!',
        ]);
        $this->actingAs($this->admin)->post(route('siswa.store'), $payload);
        $siswa = Siswa::where('nis', '99887709')->first();
        $this->assertEquals('fajarnugraha@myskanic.com', $siswa->user->email);

        Auth::logout();
        session()->flush();

        // 1. Login with full email
        $resLoginFull = $this->post(route('proseslogin'), [
            'email'    => 'fajarnugraha@myskanic.com',
            'password' => 'Password123!',
        ]);
        $resLoginFull->assertRedirect();
        $this->assertAuthenticatedAs($siswa->user);

        Auth::logout();
        session()->flush();

        // 2. Login with username only
        $resLoginUsername = $this->post(route('proseslogin'), [
            'email'    => 'fajarnugraha',
            'password' => 'Password123!',
        ]);
        $resLoginUsername->assertRedirect();
        $this->assertAuthenticatedAs($siswa->user);
    }

    /**
     * 8. Legacy student with NIS email can still login.
     */
    public function test_legacy_student_with_nis_email_can_still_login(): void
    {
        $legacyUser = User::create([
            'name'                 => 'Siswa Lama',
            'email'                => '212127999@myskanic.com',
            'password'             => Hash::make('PasswordLama123!'),
            'role'                 => 'siswa',
            'status'               => 'active',
            'must_change_password' => false,
        ]);

        Siswa::create([
            'user_id'       => $legacyUser->id,
            'nis'           => '212127999',
            'nama'          => 'Siswa Lama',
            'jenis_kelamin' => 'L',
            'kelas_id'      => $this->kelas->id,
            'status'        => 'aktif',
        ]);

        Auth::logout();
        session()->flush();

        // Login using full NIS email
        $res1 = $this->post(route('proseslogin'), [
            'email'    => '212127999@myskanic.com',
            'password' => 'PasswordLama123!',
        ]);
        $res1->assertRedirect();
        $this->assertAuthenticatedAs($legacyUser);

        Auth::logout();
        session()->flush();

        // Login using NIS only
        $res2 = $this->post(route('proseslogin'), [
            'email'    => '212127999',
            'password' => 'PasswordLama123!',
        ]);
        $res2->assertRedirect();
        $this->assertAuthenticatedAs($legacyUser);
    }
}
