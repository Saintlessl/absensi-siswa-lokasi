<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\ParentAccountActivation;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParentAccountActivationSecurityTest extends TestCase
{
    /**
     * 1 & 2. Pengguna belum login (guest) membuka halaman aktivasi dengan token valid:
     * Berhasil dimuat dengan HTTP 200, tanpa crash HTTP 500, dan menampilkan identitas akun.
     */
    public function test_guest_can_access_parent_activation_page_with_valid_token_without_http_500()
    {
        $this->assertFalse(Auth::check(), 'Harus dijalankan dalam session guest.');

        $user = User::create([
            'name'                 => 'Bapak Supriyadi',
            'email'                => 'wali.supriyadi.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('Secret1234!'),
            'role'                 => 'orangtua',
            'status'               => 'pending',
            'must_change_password' => false,
            'jenis_kelamin'        => 'L',
        ]);

        $rawToken = Str::random(64);
        ParentAccountActivation::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawToken),
            'expires_at'   => now()->addDays(7),
            'activated_at' => null,
        ]);

        $response = $this->get(route('parent.activate.show', $rawToken));

        $response->assertStatus(200);
        $response->assertSee('Aktivasi Akun Orang Tua');
        $response->assertSee('Bapak Supriyadi');
        $response->assertSee($user->email);
        $response->assertSee('ORANG TUA');
        $response->assertSee('Aktifkan Akun Sekarang');
    }

    /**
     * 3. Token invalid ditangani dengan benar (bukan HTTP 500 / 404 crash).
     */
    public function test_invalid_token_displays_friendly_error_page_without_http_500()
    {
        $this->assertFalse(Auth::check());

        $invalidToken = 'token-yang-jelas-tidak-terdaftar-' . Str::random(32);

        $response = $this->get(route('parent.activate.show', $invalidToken));

        $response->assertStatus(200);
        $response->assertSee('Tautan Tidak Valid atau Kedaluwarsa');
        $response->assertSee('Kembali ke Halaman Masuk');
        $response->assertDontSee('Aktifkan Akun Sekarang');
    }

    /**
     * 4. Token kedaluwarsa ditangani dengan benar.
     */
    public function test_expired_token_displays_friendly_error_page()
    {
        $user = User::create([
            'name'                 => 'Ibu Kartini',
            'email'                => 'wali.kartini.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('Secret1234!'),
            'role'                 => 'orangtua',
            'status'               => 'pending',
            'must_change_password' => false,
            'jenis_kelamin'        => 'P',
        ]);

        $rawToken = Str::random(64);
        ParentAccountActivation::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawToken),
            'expires_at'   => now()->subDay(), // Expired yesterday
            'activated_at' => null,
        ]);

        $response = $this->get(route('parent.activate.show', $rawToken));

        $response->assertStatus(200);
        $response->assertSee('Tautan Tidak Valid atau Kedaluwarsa');
        $response->assertSee('Kembali ke Halaman Masuk');
    }

    /**
     * Token yang sudah pernah digunakan ditolak.
     */
    public function test_already_activated_token_is_rejected()
    {
        $user = User::create([
            'name'                 => 'Bapak Hendra',
            'email'                => 'wali.hendra.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('Secret1234!'),
            'role'                 => 'orangtua',
            'status'               => 'active',
            'must_change_password' => false,
            'jenis_kelamin'        => 'L',
        ]);

        $rawToken = Str::random(64);
        ParentAccountActivation::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawToken),
            'expires_at'   => now()->addDays(5),
            'activated_at' => now()->subHour(), // Already activated
        ]);

        $response = $this->get(route('parent.activate.show', $rawToken));

        $response->assertStatus(200);
        $response->assertSee('Tautan Tidak Valid atau Kedaluwarsa');
    }

    /**
     * 5, 6, 7 & 8. Pengguna menyelesaikan aktivasi akun:
     * Password disimpan dengan hash, status berubah jadi active, dan dapat login setelah aktivasi.
     */
    public function test_parent_can_complete_activation_and_login_successfully()
    {
        $user = User::create([
            'name'                 => 'Bapak Wahyu Hidayat',
            'email'                => 'wali.wahyu.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('OldTemporarySecretPassword!'),
            'role'                 => 'orangtua',
            'status'               => 'pending',
            'must_change_password' => false,
            'jenis_kelamin'        => 'L',
        ]);

        $rawToken = Str::random(64);
        $activation = ParentAccountActivation::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawToken),
            'expires_at'   => now()->addDays(7),
            'activated_at' => null,
        ]);

        // Attempting to login BEFORE activation should be blocked because status is pending
        $loginBefore = $this->post(route('proseslogin'), [
            'email'    => $user->email,
            'password' => 'OldTemporarySecretPassword!',
        ]);
        $loginBefore->assertSessionHas('error');
        $this->assertFalse(Auth::check());

        // Now parent sets new password through activation endpoint
        $newPassword = 'PasswordBaru123!';
        $postResponse = $this->post(route('parent.activate.store', $rawToken), [
            'password'              => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $postResponse->assertRedirect(route('login'));
        $postResponse->assertSessionHas('success');

        // Verify database state: activated_at is set, status is active, password is valid hash
        $activation->refresh();
        $user->refresh();

        $this->assertNotNull($activation->activated_at);
        $this->assertEquals('active', $user->status);
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check($newPassword, $user->password));

        // Now parent can successfully login
        $loginAfter = $this->post(route('proseslogin'), [
            'email'       => $user->email,
            'password'    => $newPassword,
            'portal_role' => 'siswa', // Parent portal runs through portal_role siswa
        ]);

        $loginAfter->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    /**
     * 9 & 10. Halaman aktivasi tetap aman ketika tidak ada notifikasi session dan Auth::user() bernilai null.
     */
    public function test_activation_layout_safe_when_no_session_notifications_and_user_is_guest()
    {
        $user = User::create([
            'name'                 => 'Ibu Fatimah',
            'email'                => 'wali.fatimah.' . Str::random(6) . '@myskanic.com',
            'password'             => Hash::make('TempPass123!'),
            'role'                 => 'orangtua',
            'status'               => 'pending',
            'must_change_password' => false,
            'jenis_kelamin'        => 'P',
        ]);

        $rawToken = Str::random(64);
        ParentAccountActivation::create([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $rawToken),
            'expires_at'   => now()->addDays(7),
            'activated_at' => null,
        ]);

        // Explicitly clear session
        session()->flush();
        $this->assertNull(Auth::user());

        $response = $this->get(route('parent.activate.show', $rawToken));

        $response->assertStatus(200);
        $response->assertSee('Aktivasi Akun Orang Tua');
    }
}
