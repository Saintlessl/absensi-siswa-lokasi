<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolBrandingLogoAndFaviconTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        SchoolSetting::clearSettingCache();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.branding.test@skanic.com'],
            ['name' => 'Admin Branding Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $this->student = User::firstOrCreate(
            ['email' => 'siswa.branding.test@skanic.com'],
            ['name' => 'Siswa Branding Test', 'password' => bcrypt('password123'), 'role' => 'siswa', 'status' => 'active']
        );
    }

    public function test_default_logo_and_favicon_fallback(): void
    {
        SchoolSetting::query()->whereIn('key', ['app_logo', 'app_favicon'])->delete();
        SchoolSetting::clearSettingCache();

        $this->assertFalse(SchoolSetting::hasCustomLogo());
        $this->assertFalse(SchoolSetting::hasCustomFavicon());
        $this->assertStringContainsString('img/school-crest.png', SchoolSetting::getLogoUrl());
        $this->assertStringContainsString('img/logo.png', SchoolSetting::getFaviconUrl());
    }

    public function test_admin_can_upload_custom_logo_and_favicon(): void
    {
        $logo = UploadedFile::fake()->image('custom_school_logo.png', 300, 300);
        $favicon = UploadedFile::fake()->create('custom_favicon.ico', 32, 'image/x-icon');

        $response = $this->actingAs($this->admin)->post(route('settings.school.update'), [
            'jam_masuk'    => '07:00',
            'jam_pulang'   => '15:00',
            'nama_sekolah' => 'SMK Negeri 1 Ciomas Unggul',
            'logo'         => $logo,
            'favicon'      => $favicon,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        SchoolSetting::clearSettingCache();

        $this->assertTrue(SchoolSetting::hasCustomLogo());
        $this->assertTrue(SchoolSetting::hasCustomFavicon());

        $logoPath = SchoolSetting::value('app_logo');
        $faviconPath = SchoolSetting::value('app_favicon');

        $this->assertNotEmpty($logoPath);
        $this->assertNotEmpty($faviconPath);

        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists($faviconPath);

        $this->assertStringContainsString('storage/' . $logoPath, SchoolSetting::getLogoUrl());
        $this->assertStringContainsString('storage/' . $faviconPath, SchoolSetting::getFaviconUrl());

        // Verify dashboard layout renders the custom logo and favicon
        $dashResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee($logoPath);
        $dashResponse->assertSee($faviconPath);
    }

    public function test_admin_can_reset_custom_logo_and_favicon_to_default(): void
    {
        // First, set custom logo & favicon
        $fakeLogoPath = 'branding/test_logo_to_remove.png';
        $fakeFaviconPath = 'branding/test_fav_to_remove.png';
        Storage::disk('public')->put($fakeLogoPath, 'fake-logo-content');
        Storage::disk('public')->put($fakeFaviconPath, 'fake-fav-content');

        SchoolSetting::updateOrCreate(['key' => 'app_logo'], ['value' => $fakeLogoPath]);
        SchoolSetting::updateOrCreate(['key' => 'app_favicon'], ['value' => $fakeFaviconPath]);
        SchoolSetting::clearSettingCache();

        $this->assertTrue(SchoolSetting::hasCustomLogo());
        $this->assertTrue(SchoolSetting::hasCustomFavicon());

        // Now submit reset
        $response = $this->actingAs($this->admin)->post(route('settings.school.update'), [
            'jam_masuk'       => '07:15',
            'jam_pulang'      => '14:55',
            'remove_logo'     => 1,
            'remove_favicon'  => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        SchoolSetting::clearSettingCache();

        Storage::disk('public')->assertMissing($fakeLogoPath);
        Storage::disk('public')->assertMissing($fakeFaviconPath);

        $this->assertFalse(SchoolSetting::hasCustomLogo());
        $this->assertFalse(SchoolSetting::hasCustomFavicon());

        $this->assertStringContainsString('img/school-crest.png', SchoolSetting::getLogoUrl());
        $this->assertStringContainsString('img/logo.png', SchoolSetting::getFaviconUrl());
    }

    public function test_logo_validation_rejects_oversized_file(): void
    {
        $oversizedLogo = UploadedFile::fake()->create('huge_logo.png', 3000, 'image/png'); // 3MB

        $response = $this->actingAs($this->admin)->post(route('settings.school.update'), [
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
            'logo'       => $oversizedLogo,
        ]);

        $response->assertSessionHasErrors(['logo']);
    }

    public function test_non_admin_cannot_update_branding(): void
    {
        $teacher = User::firstOrCreate(
            ['email' => 'guru.branding.test@skanic.com'],
            ['name' => 'Guru Branding Test', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $response = $this->actingAs($teacher)->post(route('settings.school.update'), [
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
        ]);

        $response->assertStatus(403);
    }
}
