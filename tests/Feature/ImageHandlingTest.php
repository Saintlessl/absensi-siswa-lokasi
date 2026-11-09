<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Image handling contract: images only render when the file really exists
 * on the public disk; otherwise null => caller renders a placeholder.
 */
class ImageHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_url_returns_asset_url_for_existing_public_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/ada.png', 'x');

        $url = image_url('photos/ada.png');
        $this->assertNotNull($url);
        $this->assertStringContainsString('/storage/photos/ada.png', $url);
    }

    public function test_image_url_returns_null_for_missing_file_so_no_broken_image(): void
    {
        Storage::fake('public');
        $this->assertNull(image_url('photos/ hilang-tidak-ada.png'));
        $this->assertNull(image_url(null));
        $this->assertNull(image_url(''));
    }

    public function test_image_url_normalizes_windows_backslash_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('a/b.png', 'x');
        $this->assertStringContainsString('/storage/a/b.png', image_url('a\\b.png'));
    }

    public function test_image_url_passes_through_absolute_urls(): void
    {
        $this->assertSame('https://cdn.example/x.png', image_url('https://cdn.example/x.png'));
    }

    public function test_image_tag_falls_back_to_initials_placeholder(): void
    {
        Storage::fake('public');
        $html = image_tag(null, 'Budi Santoso');
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('BU', $html);

        Storage::disk('public')->put('f.png', 'x');
        $html2 = image_tag('f.png', 'Budi Santoso');
        $this->assertStringContainsString('<img', $html2);
        $this->assertStringContainsString('loading="lazy"', $html2);
    }

    public function test_uploaded_image_round_trips_and_stays_visible(): void
    {
        // Simulates the store->display contract used by every image upload form:
        // store on public disk, save relative path, render URL that resolves to a real file.
        Storage::fake('public');
        $path = UploadedFile::fake()->image('cover.png')->store('portfolios/covers', 'public');

        $this->assertTrue(Storage::disk('public')->exists($path));
        $url = image_url($path);
        $this->assertNotNull($url, 'stored upload must produce a renderable URL');
    }

    public function test_pkl_logbook_photo_url_survives_and_renders_in_history(): void
    {
        // Covered end-to-end in PklPlacementSystemTest::test_logbook_photo_visible_in_history_and_no_path_leak;
        // here assert blade view uses the guarded helper.
        $src = file_get_contents(resource_path('views/pkl/advisor_logbooks.blade.php'));
        $this->assertStringContainsString("image_url(\$log->foto_dokumentasi_path)", $src);
        $src2 = file_get_contents(resource_path('views/pkl/student_portal.blade.php'));
        $this->assertStringContainsString("image_url(\$log->foto_dokumentasi_path)", $src2);
    }
}
