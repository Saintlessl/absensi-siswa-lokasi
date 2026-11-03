<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use App\Services\SalutationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalutationAndGenderSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected SalutationService $salutationService;
    protected User $superadmin;
    protected Jurusan $jurusan;
    protected Kelas $kelas;
    protected Siswa $dummySiswa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->salutationService = app(SalutationService::class);

        $this->superadmin = User::firstOrCreate(
            ['email' => 'superadmin.gender.test@skanic.com'],
            [
                'name' => 'Super Administrator Test',
                'password' => bcrypt('password123'),
                'role' => 'superadmin',
                'status' => 'active',
                'jenis_kelamin' => 'L',
            ]
        );

        $this->jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'GENDER_TEST'],
            ['nama_jurusan' => 'Jurusan Gender Test', 'is_active' => true]
        );

        $this->kelas = Kelas::firstOrCreate(
            ['nama_kelas' => '10 Gender Test 1'],
            [
                'jurusan_id' => $this->jurusan->id,
                'tingkat' => '10',
                'nomor_rombel' => '1',
                'tahun_ajaran' => '2026/2027',
                'qr_token' => 'qr-gender-test-01',
                'jumlah_siswa' => 30,
            ]
        );

        $this->dummySiswa = Siswa::create([
            'user_id' => $this->superadmin->id,
            'nis' => 'NIS-DUMMY-' . uniqid(),
            'nama' => 'Dummy Siswa',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'biometric_status' => 'not_enrolled',
        ]);
    }

    /**
     * Test Teacher Salutations (Pak for L, Bu for P, name-only fallback for null).
     */
    public function test_teacher_salutations()
    {
        // 1. Male teacher
        $maleUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        $maleGuru = Guru::create([
            'user_id' => $maleUser->id,
            'nama' => 'Budi Santoso',
            'nip' => 'NIP-MALE-' . uniqid(),
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]);

        $this->assertEquals('Pak', $this->salutationService->getHonorific($maleUser));
        $this->assertEquals('Pak Budi Santoso', $this->salutationService->getSalutationName($maleUser));
        $this->assertEquals('Selamat datang kembali, Pak Budi Santoso!', $this->salutationService->getWelcomeGreeting($maleUser));
        $this->assertEquals('Pak Budi Santoso', $maleGuru->salutation_name);
        $this->assertEquals('Pak', $maleGuru->salutation);
        $this->assertEquals('Laki-laki', $maleGuru->gender_label);

        // 2. Female teacher
        $femaleUser = User::create([
            'name' => 'Rina Wijaya',
            'email' => 'rina.wijaya.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        $femaleGuru = Guru::create([
            'user_id' => $femaleUser->id,
            'nama' => 'Rina Wijaya',
            'nip' => 'NIP-FEMALE-' . uniqid(),
            'jenis_kelamin' => 'P',
            'is_active' => true,
        ]);

        $this->assertEquals('Bu', $this->salutationService->getHonorific($femaleUser));
        $this->assertEquals('Bu Rina Wijaya', $this->salutationService->getSalutationName($femaleUser));
        $this->assertEquals('Selamat datang kembali, Bu Rina Wijaya!', $this->salutationService->getWelcomeGreeting($femaleUser));
        $this->assertEquals('Bu Rina Wijaya', $femaleGuru->salutation_name);
        $this->assertEquals('Bu', $femaleGuru->salutation);
        $this->assertEquals('Perempuan', $femaleGuru->gender_label);

        // 3. Legacy teacher without gender (null) -> graceful fallback without guessing
        $legacyUser = User::create([
            'name' => 'Agus Priyono',
            'email' => 'agus.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        $legacyGuru = Guru::create([
            'user_id' => $legacyUser->id,
            'nama' => 'Agus Priyono',
            'nip' => 'NIP-LEGACY-' . uniqid(),
            'is_active' => true,
        ]);
        $legacyGuru->update(['jenis_kelamin' => 'L']); // Guru table requires enum, but test fallback if null
        // Test user accessor directly with no gender on user
        $rawUser = User::create([
            'name' => 'Guru Tanpa Profil',
            'email' => 'tanpa.profil.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        $this->assertEquals('', $this->salutationService->getHonorific($rawUser));
        $this->assertEquals('Guru Tanpa Profil', $this->salutationService->getSalutationName($rawUser));
        $this->assertEquals('Selamat datang kembali, Guru Tanpa Profil!', $this->salutationService->getWelcomeGreeting($rawUser));
    }

    /**
     * Test Student Salutations: always direct name, never Pak/Bu.
     */
    public function test_student_salutations_never_use_honorific()
    {
        // 1. Male student
        $maleStudentUser = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.' . uniqid() . '@myskanic.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $maleSiswa = Siswa::create([
            'user_id' => $maleStudentUser->id,
            'nis' => 'NIS-' . uniqid(),
            'nama' => 'Ahmad Fauzi',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'biometric_status' => 'enrolled',
        ]);

        $this->assertEquals('', $this->salutationService->getHonorific($maleStudentUser));
        $this->assertEquals('Ahmad Fauzi', $this->salutationService->getSalutationName($maleStudentUser));
        $this->assertEquals('Selamat datang kembali, Ahmad Fauzi!', $this->salutationService->getWelcomeGreeting($maleStudentUser));
        $this->assertEquals('', $maleSiswa->salutation);
        $this->assertEquals('Ahmad Fauzi', $maleSiswa->salutation_name);

        // 2. Female student
        $femaleStudentUser = User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti.' . uniqid() . '@myskanic.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'status' => 'active',
        ]);
        $femaleSiswa = Siswa::create([
            'user_id' => $femaleStudentUser->id,
            'nis' => 'NIS-' . uniqid(),
            'nama' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'kelas_id' => $this->kelas->id,
            'biometric_status' => 'enrolled',
        ]);

        $this->assertEquals('', $this->salutationService->getHonorific($femaleStudentUser));
        $this->assertEquals('Siti Aminah', $this->salutationService->getSalutationName($femaleStudentUser));
        $this->assertEquals('Selamat datang kembali, Siti Aminah!', $this->salutationService->getWelcomeGreeting($femaleStudentUser));
        $this->assertEquals('', $femaleSiswa->salutation);
        $this->assertEquals('Siti Aminah', $femaleSiswa->salutation_name);
    }

    /**
     * Test Parent Salutations: Bapak for L, Ibu for P.
     */
    public function test_parent_salutations()
    {
        // 1. Father (Male parent)
        $fatherUser = User::create([
            'name' => 'Bambang Hartono',
            'email' => 'wali.' . uniqid() . '@myskanic.com',
            'password' => bcrypt('password'),
            'role' => 'orangtua',
            'status' => 'active',
            'jenis_kelamin' => 'L',
        ]);
        $father = OrangTua::create([
            'user_id' => $fatherUser->id,
            'nama' => 'Bambang Hartono',
            'jenis_kelamin' => 'L',
            'telepon' => '+6281234567891',
            'alamat' => 'Jl. Merdeka No. 1',
            'siswa_id' => $this->dummySiswa->id,
        ]);

        $this->assertEquals('Bapak', $this->salutationService->getHonorific($fatherUser));
        $this->assertEquals('Bapak Bambang Hartono', $this->salutationService->getSalutationName($fatherUser));
        $this->assertEquals('Selamat datang kembali, Bapak Bambang Hartono!', $this->salutationService->getWelcomeGreeting($fatherUser));
        $this->assertEquals('Bapak', $father->salutation);
        $this->assertEquals('Bapak Bambang Hartono', $father->salutation_name);

        // 2. Mother (Female parent)
        $motherUser = User::create([
            'name' => 'Hartati',
            'email' => 'wali.' . uniqid() . '@myskanic.com',
            'password' => bcrypt('password'),
            'role' => 'orangtua',
            'status' => 'active',
            'jenis_kelamin' => 'P',
        ]);
        $mother = OrangTua::create([
            'user_id' => $motherUser->id,
            'nama' => 'Hartati',
            'jenis_kelamin' => 'P',
            'telepon' => '+6281234567892',
            'alamat' => 'Jl. Sudirman No. 2',
            'siswa_id' => $this->dummySiswa->id,
        ]);

        $this->assertEquals('Ibu', $this->salutationService->getHonorific($motherUser));
        $this->assertEquals('Ibu Hartati', $this->salutationService->getSalutationName($motherUser));
        $this->assertEquals('Selamat datang kembali, Ibu Hartati!', $this->salutationService->getWelcomeGreeting($motherUser));
        $this->assertEquals('Ibu', $mother->salutation);
        $this->assertEquals('Ibu Hartati', $mother->salutation_name);
    }

    /**
     * Test Administrator / Superadmin Salutations: Pak / Bu based on gender.
     */
    public function test_administrator_salutations()
    {
        $adminMale = User::create([
            'name' => 'Joko Susilo',
            'email' => 'admin.joko.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'jenis_kelamin' => 'L',
        ]);

        $adminFemale = User::create([
            'name' => 'Maya Indah',
            'email' => 'admin.maya.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'jenis_kelamin' => 'P',
        ]);

        $adminUnset = User::create([
            'name' => 'Admin Legacy',
            'email' => 'admin.legacy.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'jenis_kelamin' => null,
        ]);

        $this->assertEquals('Pak Joko Susilo', $adminMale->salutation_name);
        $this->assertEquals('Bu Maya Indah', $adminFemale->salutation_name);
        $this->assertEquals('Admin Legacy', $adminUnset->salutation_name);
        $this->assertEquals('Belum diatur', $adminUnset->effective_gender_label);
    }

    /**
     * Test User Controller Store & Update with Gender validation.
     */
    public function test_user_management_controller_gender_validation()
    {
        $this->actingAs($this->superadmin);

        // 1. Invalid gender should be rejected
        $responseInvalid = $this->post(route('user'), [
            'name' => 'Invalid Gender User',
            'email' => 'invalid.gender.' . uniqid() . '@skanic.com',
            'password' => 'password123',
            'role' => 'admin',
            'jenis_kelamin' => 'Pria', // Invalid, must be L or P
        ]);
        $responseInvalid->assertSessionHasErrors('jenis_kelamin');

        // 2. Valid creation with gender L
        $uniqueEmail = 'valid.user.' . uniqid() . '@skanic.com';
        $responseValid = $this->post(route('user'), [
            'name' => 'Staff IT Baru',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'role' => 'admin',
            'jenis_kelamin' => 'L',
        ]);
        $responseValid->assertSessionHasNoErrors();

        $createdUser = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('L', $createdUser->jenis_kelamin);
        $this->assertEquals('Pak Staff IT Baru', $createdUser->salutation_name);

        // 3. Update user gender to P
        $responseUpdate = $this->post(route('user.update', ['id' => $createdUser->id]), [
            'name' => 'Staff IT Baru',
            'email' => $uniqueEmail,
            'role' => 'admin',
            'jenis_kelamin' => 'P',
        ]);
        $responseUpdate->assertSessionHasNoErrors();

        $createdUser->refresh();
        $this->assertEquals('P', $createdUser->jenis_kelamin);
        $this->assertEquals('Bu Staff IT Baru', $createdUser->salutation_name);
    }

    /**
     * Test Parent Gender update via OrangTuaController.
     */
    public function test_orang_tua_controller_update_gender()
    {
        $this->actingAs($this->superadmin);

        $parentUser = User::create([
            'name' => 'Orang Tua Update Test',
            'email' => 'wali.update.' . uniqid() . '@myskanic.com',
            'password' => bcrypt('password'),
            'role' => 'orangtua',
            'status' => 'active',
            'jenis_kelamin' => null,
        ]);

        $orangTua = OrangTua::create([
            'user_id' => $parentUser->id,
            'nama' => 'Orang Tua Update Test',
            'telepon' => '+6281122334455',
            'alamat' => 'Alamat Asli',
            'jenis_kelamin' => null,
            'siswa_id' => $this->dummySiswa->id,
        ]);

        $this->assertNull($orangTua->jenis_kelamin);

        $response = $this->post(route('orangtua.update', ['id' => $orangTua->id]), [
            'nama' => 'Ibu Ratna Dewi',
            'email' => $parentUser->email,
            'telepon' => '+6281122334455',
            'alamat' => 'Alamat Baru',
            'jenis_kelamin' => 'P',
        ]);

        $response->assertSessionHasNoErrors();
        $orangTua->refresh();
        $this->assertEquals('P', $orangTua->jenis_kelamin);
        $this->assertEquals('Ibu Ratna Dewi', $orangTua->salutation_name);
    }

    /**
     * Test Student and Parent Creation with Genders in SiswaController.
     */
    public function test_student_and_parent_creation_with_gender()
    {
        $this->actingAs($this->superadmin);

        $uniqueNIS = (string) rand(100000, 999999);
        $response = $this->post(route('siswa.store'), [
            'nis' => $uniqueNIS,
            'nisn' => 'NISN-' . $uniqueNIS,
            'nama' => 'Siswa Baru Gender Test',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas->id,
            'username' => 'siswa' . $uniqueNIS,
            'password' => 'password123',
            'parent_name' => 'Bapak Siswa Baru',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081987654321',
            'parent_address' => 'Jl. Mawar No. 10',
        ]);

        $response->assertSessionHasNoErrors();

        $siswa = Siswa::where('nis', $uniqueNIS)->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('L', $siswa->jenis_kelamin);
        $this->assertEquals('Siswa Baru Gender Test', $siswa->salutation_name);

        $parent = $siswa->orangtua->first();
        $this->assertNotNull($parent);
        $this->assertEquals('L', $parent->jenis_kelamin);
        $this->assertEquals('Bapak Siswa Baru', $parent->salutation_name);
    }

    /**
     * Test Dashboard Banner renders salutation name and greeting.
     */
    public function test_dashboard_renders_salutation_banner()
    {
        // 1. Male teacher login
        $maleUser = User::create([
            'name' => 'Budi Guru Ganteng',
            'email' => 'budi.banner.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        Guru::create([
            'user_id' => $maleUser->id,
            'nama' => 'Budi Guru Ganteng',
            'nip' => 'NIP-BANNER-' . uniqid(),
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]);

        $responseGuru = $this->actingAs($maleUser)->get(route('dashboard'));
        $responseGuru->assertStatus(200);
        $responseGuru->assertSee('Selamat datang kembali, Pak Budi Guru Ganteng!');

        // 2. Female teacher login
        $femaleUser = User::create([
            'name' => 'Rina Guru Cantik',
            'email' => 'rina.banner.' . uniqid() . '@skanic.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'status' => 'active',
        ]);
        Guru::create([
            'user_id' => $femaleUser->id,
            'nama' => 'Rina Guru Cantik',
            'nip' => 'NIP-BANNER-F-' . uniqid(),
            'jenis_kelamin' => 'P',
            'is_active' => true,
        ]);

        $responseGuruF = $this->actingAs($femaleUser)->get(route('dashboard'));
        $responseGuruF->assertStatus(200);
        $responseGuruF->assertSee('Selamat datang kembali, Bu Rina Guru Cantik!');
    }
}
