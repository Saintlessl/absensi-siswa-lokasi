<?php

namespace Tests\Feature;

use App\Models\Angkatan;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentMandatoryProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $teacher;
    protected User $kaprog1;
    protected User $kaprog2;
    protected Jurusan $jurusan1;
    protected Jurusan $jurusan2;
    protected Kelas $kelas1;
    protected Kelas $kelas2;
    protected TahunAjaran $ta;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->superadmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin@myskanic.com',
            'name' => 'Super Administrator',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@myskanic.com',
            'name' => 'Administrator',
        ]);

        $this->teacher = User::factory()->create([
            'role' => 'guru',
            'email' => 'guru@myskanic.com',
            'name' => 'Guru Pengajar',
        ]);

        $this->jurusan1 = Jurusan::create([
            'kode_jurusan' => 'PPLG',
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'is_active' => true,
        ]);

        $this->jurusan2 = Jurusan::create([
            'kode_jurusan' => 'TJKT',
            'nama_jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi',
            'is_active' => true,
        ]);

        $this->ta = TahunAjaran::where('nama', '2026/2027')->first() ?? TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            [
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
                'tanggal_mulai' => '2026-07-15',
                'tanggal_selesai' => '2027-06-30',
                'semester' => 'ganjil',
                'status' => 'aktif',
                'is_active' => true,
            ]
        );

        $this->kelas1 = Kelas::create([
            'jurusan_id' => $this->jurusan1->id,
            'tahun_ajaran_id' => $this->ta->id,
            'nama_kelas' => '10 PPLG 1',
            'tingkat' => 10,
            'is_active' => true,
        ]);

        $this->kelas2 = Kelas::create([
            'jurusan_id' => $this->jurusan2->id,
            'tahun_ajaran_id' => $this->ta->id,
            'nama_kelas' => '10 TJKT 1',
            'tingkat' => 10,
            'is_active' => true,
        ]);

        $this->kaprog1 = User::factory()->create([
            'role' => 'kaprog',
            'jurusan_id' => $this->jurusan1->id,
            'email' => 'kaprog.pplg@myskanic.com',
            'name' => 'Kaprog PPLG',
        ]);

        $this->kaprog2 = User::factory()->create([
            'role' => 'kaprog',
            'jurusan_id' => $this->jurusan2->id,
            'email' => 'kaprog.tjkt@myskanic.com',
            'name' => 'Kaprog TJKT',
        ]);
    }

    /**
     * A. Tambah Siswa Tanpa Foto DITOLAK
     */
    public function test_creating_student_without_photo_is_strictly_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100001',
            'nisn' => '0010000001',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'no_telp_siswa' => '081234567890',
            'parent_name' => 'Ayah Budi',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Merdeka No 1',
            // Missing 'photo'
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertDatabaseMissing('siswas', ['nis' => '100001']);
    }

    /**
     * B. Tambah Siswa Dengan Foto Valid Berhasil dan Foto Disimpan di Private Storage
     */
    public function test_creating_student_with_valid_photo_succeeds_and_stores_in_private_storage(): void
    {
        $photo = UploadedFile::fake()->image('pasfoto_budi.jpg', 400, 400);

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100002',
            'nisn' => '0010000002',
            'nama' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'no_telp_siswa' => '081234567890',
            'photo' => $photo,
            'parent_name' => 'Ayah Budi',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Merdeka No 1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $siswa = Siswa::where('nis', '100002')->first();
        $this->assertNotNull($siswa);
        $this->assertNotNull($siswa->photo_path);
        $this->assertTrue(Storage::disk('local')->exists($siswa->photo_path));
        $this->assertNotNull($siswa->photo_url);
        $this->assertEquals(route('siswa.photo', ['id' => $siswa->id]), $siswa->photo_url);
    }

    /**
     * C. Upload File Tidak Valid (Resolusi di bawah 300x300 atau format salah) DITOLAK
     */
    public function test_uploading_photo_below_300x300_resolution_is_rejected(): void
    {
        // 200x200 is below 300x300 minimum
        $smallPhoto = UploadedFile::fake()->image('small_avatar.jpg', 200, 200);

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100003',
            'nisn' => '0010000003',
            'nama' => 'Cahya Kamila',
            'jenis_kelamin' => 'P',
            'kelas_id' => $this->kelas1->id,
            'no_telp_siswa' => '081234567890',
            'photo' => $smallPhoto,
            'parent_name' => 'Ibu Cahya',
            'parent_jenis_kelamin' => 'P',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Mawar No 1',
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertDatabaseMissing('siswas', ['nis' => '100003']);
    }

    public function test_uploading_non_image_file_is_rejected(): void
    {
        $fakeDoc = UploadedFile::fake()->create('malicious.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100004',
            'nisn' => '0010000004',
            'nama' => 'Doni Darmawan',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'no_telp_siswa' => '081234567890',
            'photo' => $fakeDoc,
            'parent_name' => 'Ayah Doni',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Melati No 2',
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertDatabaseMissing('siswas', ['nis' => '100004']);
    }

    /**
     * D. Edit Foto Siswa: Ganti Foto Baru, Foto Lama Dihapus Aman
     */
    public function test_updating_student_photo_replaces_old_photo_safely(): void
    {
        $oldPhoto = UploadedFile::fake()->image('old_photo.jpg', 350, 350);
        $oldPath = $oldPhoto->store('siswa-photos', 'local');

        $studentUser = User::factory()->create([
            'role' => 'siswa',
            'must_change_password' => false,
        ]);
        $siswa = Siswa::create([
            'user_id' => $studentUser->id,
            'nis' => '10005',
            'nisn' => '0010000005',
            'nama' => 'Eka Prasetya',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => $oldPath,
            'no_telp_siswa' => '081234567890',
            'status' => 'aktif',
            'is_biometric_exempt' => true,
        ]);

        $parentUser = User::factory()->create([
            'role' => 'orangtua',
            'must_change_password' => false,
        ]);
        $parent = OrangTua::create([
            'user_id' => $parentUser->id,
            'nama' => 'Ayah Eka',
            'jenis_kelamin' => 'L',
            'telepon' => '081234567891',
            'alamat' => 'Alamat Eka',
            'siswa_id' => $siswa->id,
        ]);
        $siswa->orangtua()->attach($parent->id);

        $this->assertTrue(Storage::disk('local')->exists($oldPath));

        $newPhoto = UploadedFile::fake()->image('new_photo.jpg', 400, 400);

        $response = $this->actingAs($this->admin)->post(route('siswa.update', ['id' => $siswa->id]), [
            'nis' => '10005',
            'nisn' => '0010000005',
            'nama' => 'Eka Prasetya Updated',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'username' => 'ekaprasetya',
            'photo' => $newPhoto,
            'parent_name' => 'Ayah Eka',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567891',
            'parent_address' => 'Alamat Eka',
        ]);

        $response->assertRedirect();
        $siswa->refresh();

        $this->assertNotEquals($oldPath, $siswa->photo_path);
        $this->assertTrue(Storage::disk('local')->exists($siswa->photo_path));
        // Old photo file was deleted
        $this->assertFalse(Storage::disk('local')->exists($oldPath));
    }

    /**
     * E. Dua Siswa dengan Nama Sama Tetap Dapat Terdaftar dengan Pembeda Unik
     */
    public function test_students_with_identical_names_have_unique_identities_and_photos(): void
    {
        $photo1 = UploadedFile::fake()->image('fajar1.jpg', 300, 300);
        $photo2 = UploadedFile::fake()->image('fajar2.jpg', 300, 300);

        // Student 1: Fajar Nugraha (PPLG)
        $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100006',
            'nisn' => '0010000006',
            'nama' => 'Fajar Nugraha',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'no_telp_siswa' => '081234567890',
            'photo' => $photo1,
            'parent_name' => 'Ayah Fajar 1',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567891',
            'parent_address' => 'Jl. Sawah 1',
        ]);

        // Student 2: Fajar Nugraha (TJKT)
        $this->actingAs($this->admin)->post(route('siswa.store'), [
            'nis' => '100007',
            'nisn' => '0010000007',
            'nama' => 'Fajar Nugraha',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas2->id,
            'no_telp_siswa' => '081234567892',
            'photo' => $photo2,
            'parent_name' => 'Ayah Fajar 2',
            'parent_jenis_kelamin' => 'L',
            'parent_phone' => '081234567893',
            'parent_address' => 'Jl. Kebun 2',
        ]);

        $students = Siswa::where('nama', 'Fajar Nugraha')->get();
        $this->assertCount(2, $students);
        $this->assertNotEquals($students[0]->nis, $students[1]->nis);
        $this->assertNotEquals($students[0]->photo_path, $students[1]->photo_path);

        // Search options endpoint returns both students with respective photos and class info
        $searchRes = $this->actingAs($this->admin)->getJson(route('options.select.siswa', ['q' => 'Fajar Nugraha']));
        $searchRes->assertOk();
        $data = $searchRes->json('data');
        $this->assertCount(2, $data);
        $this->assertNotNull($data[0]['photo_url']);
        $this->assertNotNull($data[1]['photo_url']);
        $this->assertNotEquals($data[0]['id'], $data[1]['id']);
    }

    /**
     * F. Integrasi Search Option: returns photo_url, initial, kelas, jurusan
     */
    public function test_select_option_endpoint_returns_photo_and_distinct_academic_details(): void
    {
        $photo = UploadedFile::fake()->image('gilang.jpg', 300, 300);
        $path = $photo->store('siswa-photos', 'local');

        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '10008',
            'nisn' => '0010000008',
            'nama' => 'Gilang Ramadhan',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => $path,
            'no_telp_siswa' => '081234567890',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->teacher)->getJson(route('options.select.siswa', ['q' => 'Gilang']));
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $siswa->id,
            'nama' => 'Gilang Ramadhan',
            'nis' => '10008',
            'kelas' => '10 PPLG 1',
            'jurusan' => 'PPLG',
            'photo_url' => route('siswa.photo', ['id' => $siswa->id]),
            'initial' => 'G',
        ]);
    }

    /**
     * G. Permission and Authorization on Protected Photo Endpoint
     */
    public function test_unauthenticated_user_cannot_access_student_photo(): void
    {
        $photo = UploadedFile::fake()->image('secret.jpg', 300, 300);
        $path = $photo->store('siswa-photos', 'local');

        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '10009',
            'nama' => 'Hani Handayani',
            'jenis_kelamin' => 'P',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => $path,
            'status' => 'aktif',
        ]);

        // Unauthenticated request
        $response = $this->get(route('siswa.photo', ['id' => $siswa->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_kaprog_cannot_access_student_photo_outside_their_jurusan(): void
    {
        $photo = UploadedFile::fake()->image('tjkt_student.jpg', 300, 300);
        $path = $photo->store('siswa-photos', 'local');

        $user = User::factory()->create(['role' => 'siswa']);
        // Student in Jurusan 2 (TJKT)
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '10010',
            'nama' => 'Indra Lesmana',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas2->id,
            'photo_path' => $path,
            'status' => 'aktif',
        ]);

        // Kaprog 1 is PPLG, trying to view TJKT student's photo
        $response = $this->actingAs($this->kaprog1)->get(route('siswa.photo', ['id' => $siswa->id]));
        $response->assertForbidden();

        // Kaprog 2 is TJKT, allowed to view their own jurusan's student
        $response2 = $this->actingAs($this->kaprog2)->get(route('siswa.photo', ['id' => $siswa->id]));
        $response2->assertOk();
    }

    public function test_student_can_only_view_their_own_photo(): void
    {
        $photo1 = UploadedFile::fake()->image('s1.jpg', 300, 300);
        $path1 = $photo1->store('siswa-photos', 'local');
        $u1 = User::factory()->create([
            'role' => 'siswa',
            'must_change_password' => false,
        ]);
        $s1 = Siswa::create([
            'user_id' => $u1->id,
            'nis' => '10011',
            'nama' => 'Siswa Satu',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => $path1,
            'status' => 'aktif',
            'is_biometric_exempt' => true,
        ]);

        $photo2 = UploadedFile::fake()->image('s2.jpg', 300, 300);
        $path2 = $photo2->store('siswa-photos', 'local');
        $u2 = User::factory()->create([
            'role' => 'siswa',
            'must_change_password' => false,
        ]);
        $s2 = Siswa::create([
            'user_id' => $u2->id,
            'nis' => '10012',
            'nama' => 'Siswa Dua',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => $path2,
            'status' => 'aktif',
            'is_biometric_exempt' => true,
        ]);

        // Student 1 views their own photo -> allowed
        $resSelf = $this->actingAs($u1)->get(route('siswa.photo', ['id' => $s1->id]));
        $resSelf->assertOk();

        // Student 1 tries to view Student 2 photo -> forbidden
        $resOther = $this->actingAs($u1)->get(route('siswa.photo', ['id' => $s2->id]));
        $resOther->assertForbidden();
    }

    /**
     * H. Siswa Lama Tanpa Foto Tetap Dapat Diakses & Placeholder SVG Bekerja
     */
    public function test_legacy_student_without_photo_renders_fallback_placeholder(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '10013',
            'nama' => 'Joko Widodo',
            'jenis_kelamin' => 'L',
            'kelas_id' => $this->kelas1->id,
            'photo_path' => null, // No photo
            'status' => 'aktif',
        ]);

        // Index page loads smoothly
        $indexRes = $this->actingAs($this->admin)->get(route('siswa'));
        $indexRes->assertOk();
        $indexRes->assertSee('Joko Widodo');

        // Filter 'tanpa_foto' lists this student
        $filterRes = $this->actingAs($this->admin)->get(route('siswa', ['tanpa_foto' => 1]));
        $filterRes->assertOk();
        $filterRes->assertSee('Joko Widodo');

        // Photo route serves fallback SVG placeholder
        $photoRes = $this->actingAs($this->admin)->get(route('siswa.photo', ['id' => $siswa->id]));
        $photoRes->assertOk();
        $photoRes->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('J', $photoRes->getContent());
    }
}
