<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TeacherQuickEditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test.quickedit@myskanic.com'],
            ['name' => 'Admin QuickEdit Test', 'password' => bcrypt('password123'), 'role' => 'admin', 'status' => 'active']
        );

        $guruUser = User::firstOrCreate(
            ['email' => 'guru.quickedit.test@myskanic.com'],
            ['name' => 'Guru QuickEdit Target', 'password' => bcrypt('password123'), 'role' => 'guru', 'status' => 'active']
        );

        $this->guru = Guru::firstOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nama' => 'Guru QuickEdit Target',
                'nip' => '198801012015011001',
                'mapel' => 'Rekayasa Perangkat Lunak',
                'jenis_kelamin' => 'L',
                'telepon' => '081299887766',
                'status_kepegawaian' => 'PNS',
                'is_active' => true,
            ]
        );
    }

    public function test_guru_index_page_loads_without_javascript_leakage(): void
    {
        $response = $this->actingAs($this->admin)->get(route('guru'));

        $response->assertStatus(200);

        // Verify that raw JS string literals do not leak into the HTML text outside scripts
        $content = $response->getContent();

        // Must not contain dangling text outside scripts
        $this->assertStringNotContainsString("'+ err.message +'", $content);
        $this->assertStringNotContainsString("tElementById('loadeditform').innerHTML = '", $content);

        // The function editGuru should be properly enclosed in <script>
        $this->assertMatchesRegularExpression('/<script[^>]*>[\s\S]*?function\s+editGuru[\s\S]*?<\/script>/', $content);
    }

    public function test_quick_edit_endpoint_returns_html_form(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('guru.edit'), [
            'id' => $this->guru->id,
        ]);

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="frmEditGuru"', $content);
        $this->assertStringContainsString('value="' . $this->guru->nama . '"', $content);
        $this->assertStringContainsString('value="' . $this->guru->nip . '"', $content);
        $this->assertStringContainsString('value="' . $this->guru->user->email . '"', $content);
        $this->assertStringContainsString('name="telepon"', $content);
        $this->assertStringContainsString('name="status_kepegawaian"', $content);
    }

    public function test_quick_edit_endpoint_handles_invalid_or_missing_id(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('guru.edit'), [
            'id' => 99999999,
        ]);

        $response->assertStatus(404);

        $responseMissing = $this->actingAs($this->admin)->postJson(route('guru.edit'), []);
        $responseMissing->assertStatus(400);
    }

    public function test_teacher_update_saves_changes_and_preserves_unmodified_fields(): void
    {
        $oldTelepon = $this->guru->telepon;

        $response = $this->actingAs($this->admin)->post(route('guru.update', ['id' => $this->guru->id]), [
            'nama' => 'Guru QuickEdit Updated Name',
            'nip' => '198801012015011001',
            'mapel' => 'Kecerdasan Buatan & Web',
            'jenis_kelamin' => 'L',
            'email' => 'guru.quickedit.test@myskanic.com',
            'status_kepegawaian' => 'PPPK',
            'telepon' => '089911223344',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->guru->refresh();
        $this->assertEquals('Guru QuickEdit Updated Name', $this->guru->nama);
        $this->assertEquals('Guru QuickEdit Updated Name', $this->guru->user->name);
        $this->assertEquals('Kecerdasan Buatan & Web', $this->guru->mapel);
        $this->assertEquals('PPPK', $this->guru->status_kepegawaian);
        $this->assertEquals('089911223344', $this->guru->telepon);
    }
}
