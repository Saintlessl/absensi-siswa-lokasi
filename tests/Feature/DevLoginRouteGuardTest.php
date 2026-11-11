<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevLoginRouteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_dev_login(): void
    {
        $resp = $this->get('/dev-login?role=superadmin');
        // guest middleware: redirected away from the route, no session login
        $this->assertTrue($resp->isRedirection());
        $this->assertGuest();
    }

    public function test_non_superadmin_cannot_use_dev_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resp = $this->actingAs($admin)->get('/dev-login?role=superadmin');
        $resp->assertRedirect(route('dashboard'));
        // still the original admin, not swapped to superadmin
        $this->assertAuthenticatedAs($admin);
    }

    public function test_superadmin_can_use_dev_login(): void
    {
        $super = User::factory()->create(['role' => 'superadmin']);
        User::factory()->create(['role' => 'admin', 'name' => 'Target Admin']);
        $resp = $this->actingAs($super)->get('/dev-login?role=admin');
        $resp->assertRedirect(route('dashboard'));
        $this->assertEquals('admin', auth()->user()->role);
    }
}
