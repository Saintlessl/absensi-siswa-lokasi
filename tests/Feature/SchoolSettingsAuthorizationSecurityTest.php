<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SchoolSettingsAuthorizationSecurityTest extends TestCase
{
    private User $superadmin;
    private User $admin;
    private User $kaprog;
    private User $guru;
    private User $siswa;
    private User $orangtua;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim'],
            ['kode_jurusan' => 'PPLG', 'singkatan' => 'PPLG', 'is_active' => true]
        );

        $this->superadmin = User::firstOrCreate(
            ['email' => 'superadmin.test@myskanic.com'],
            ['name' => 'Superadmin Test', 'password' => Hash::make('password123'), 'role' => 'superadmin', 'status' => 'active', 'must_change_password' => false]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test@myskanic.com'],
            ['name' => 'Admin Test', 'password' => Hash::make('password123'), 'role' => 'admin', 'status' => 'active', 'must_change_password' => false]
        );

        $this->kaprog = User::firstOrCreate(
            ['email' => 'kaprog.test@myskanic.com'],
            ['name' => 'Kaprog Test', 'password' => Hash::make('password123'), 'role' => 'kaprog', 'jurusan_id' => $jurusan->id, 'status' => 'active', 'must_change_password' => false]
        );

        $this->guru = User::firstOrCreate(
            ['email' => 'guru.test@myskanic.com'],
            ['name' => 'Guru Test', 'password' => Hash::make('password123'), 'role' => 'guru', 'status' => 'active', 'must_change_password' => false]
        );

        $this->siswa = User::firstOrCreate(
            ['email' => 'siswa.test@myskanic.com'],
            ['name' => 'Siswa Test', 'password' => Hash::make('password123'), 'role' => 'siswa', 'status' => 'active', 'must_change_password' => false]
        );

        $this->orangtua = User::firstOrCreate(
            ['email' => 'orangtua.test@myskanic.com'],
            ['name' => 'Orangtua Test', 'password' => Hash::make('password123'), 'role' => 'orangtua', 'status' => 'active', 'must_change_password' => false]
        );
    }

    /**
     * 1. Guest: Access GET & direct POST rejected.
     */
    public function test_guest_cannot_access_or_modify_school_settings()
    {
        $getResponse = $this->get(route('settings.school'));
        $getResponse->assertRedirect(route('login'));

        $postResponse = $this->post(route('settings.school.update'), [
            'jam_masuk'  => '08:00',
            'jam_pulang' => '15:00',
        ]);
        $postResponse->assertRedirect(route('login'));
    }

    /**
     * 2. Siswa: Access GET & direct POST rejected.
     */
    public function test_siswa_cannot_access_or_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->siswa)->get(route('settings.school'));
        $this->assertTrue(in_array($getResponse->status(), [302, 403]));

        $postResponse = $this->actingAs($this->siswa)->post(route('settings.school.update'), [
            'jam_masuk'  => '08:30',
            'jam_pulang' => '12:00',
        ]);
        $this->assertTrue(in_array($postResponse->status(), [302, 403]));

        // Direct JSON API attempt
        $jsonResponse = $this->actingAs($this->siswa)->postJson(route('settings.school.update'), [
            'jam_masuk'  => '08:30',
            'jam_pulang' => '12:00',
        ]);
        $jsonResponse->assertStatus(403);
    }

    /**
     * 3. Orang Tua: Access GET & direct POST rejected.
     */
    public function test_orangtua_cannot_access_or_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->orangtua)->get(route('settings.school'));
        $this->assertTrue(in_array($getResponse->status(), [302, 403]));

        $postResponse = $this->actingAs($this->orangtua)->post(route('settings.school.update'), [
            'jam_masuk'  => '09:00',
            'jam_pulang' => '13:00',
        ]);
        $this->assertTrue(in_array($postResponse->status(), [302, 403]));

        $jsonResponse = $this->actingAs($this->orangtua)->postJson(route('settings.school.update'), [
            'jam_masuk'  => '09:00',
            'jam_pulang' => '13:00',
        ]);
        $jsonResponse->assertStatus(403);
    }

    /**
     * 4. Guru: Access GET & direct POST rejected.
     */
    public function test_guru_cannot_access_or_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->guru)->get(route('settings.school'));
        $this->assertTrue(in_array($getResponse->status(), [302, 403]));

        $postResponse = $this->actingAs($this->guru)->post(route('settings.school.update'), [
            'jam_masuk'  => '08:15',
            'jam_pulang' => '14:00',
        ]);
        $this->assertTrue(in_array($postResponse->status(), [302, 403]));

        $jsonResponse = $this->actingAs($this->guru)->postJson(route('settings.school.update'), [
            'jam_masuk'  => '08:15',
            'jam_pulang' => '14:00',
        ]);
        $jsonResponse->assertStatus(403);
    }

    /**
     * 5. Kaprog: Access GET & direct POST rejected.
     */
    public function test_kaprog_cannot_access_or_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->kaprog)->get(route('settings.school'));
        $this->assertTrue(in_array($getResponse->status(), [302, 403]));

        $postResponse = $this->actingAs($this->kaprog)->post(route('settings.school.update'), [
            'jam_masuk'  => '08:15',
            'jam_pulang' => '14:00',
        ]);
        $this->assertTrue(in_array($postResponse->status(), [302, 403]));

        $jsonResponse = $this->actingAs($this->kaprog)->postJson(route('settings.school.update'), [
            'jam_masuk'  => '08:15',
            'jam_pulang' => '14:00',
        ]);
        $jsonResponse->assertStatus(403);
    }

    /**
     * 6. Admin: Access GET & POST allowed and successfully modifies settings.
     */
    public function test_admin_can_access_and_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->admin)->get(route('settings.school'));
        $getResponse->assertStatus(200);
        $getResponse->assertSee('Identitas');

        $newJamMasuk = '07:10';
        $newJamPulang = '15:15';

        $postResponse = $this->actingAs($this->admin)->post(route('settings.school.update'), [
            'jam_masuk'    => $newJamMasuk,
            'jam_pulang'   => $newJamPulang,
            'nama_sekolah' => 'SMK Negeri 1 Ciomas',
        ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('success');

        SchoolSetting::clearSettingCache();
        $this->assertEquals($newJamMasuk, SchoolSetting::value('jam_masuk'));
        $this->assertEquals($newJamPulang, SchoolSetting::value('jam_pulang'));
    }

    /**
     * 7. Super Admin: Access GET & POST allowed and successfully modifies settings.
     */
    public function test_superadmin_can_access_and_modify_school_settings()
    {
        $getResponse = $this->actingAs($this->superadmin)->get(route('settings.school'));
        $getResponse->assertStatus(200);

        $newJamMasuk = '07:05';
        $newJamPulang = '15:30';

        $postResponse = $this->actingAs($this->superadmin)->post(route('settings.school.update'), [
            'jam_masuk'    => $newJamMasuk,
            'jam_pulang'   => $newJamPulang,
            'nama_sekolah' => 'SMK Negeri 1 Ciomas',
        ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('success');

        SchoolSetting::clearSettingCache();
        $this->assertEquals($newJamMasuk, SchoolSetting::value('jam_masuk'));
        $this->assertEquals($newJamPulang, SchoolSetting::value('jam_pulang'));
    }
}
