<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Services\WhatsApp\Adapters\FonnteAdapter;
use App\Services\WhatsApp\Adapters\LogAdapter;
use App\Services\WhatsApp\Adapters\WablasAdapter;
use App\Services\WhatsApp\WhatsAppGatewayManager;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_fonnte_adapter_successful_response()
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status'  => true,
                'id'      => 'fonnte_msg_9988',
                'process' => 'success',
            ], 200),
        ]);

        $adapter = new FonnteAdapter('dummy_token_123', 'https://api.fonnte.com/send');
        $result = $adapter->send('081234567890', 'Pesan uji coba');

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('fonnte_msg_9988', $result['provider_id']);
        $this->assertNull($result['error']);
    }

    public function test_wablas_adapter_successful_response()
    {
        Http::fake([
            'https://api.wablas.com/api/send-message' => Http::response([
                'status'  => true,
                'data'    => ['id' => 'wablas_msg_1122'],
                'message' => 'success',
            ], 200),
        ]);

        $adapter = new WablasAdapter('dummy_token_456', 'https://api.wablas.com/api/send-message');
        $result = $adapter->send('081234567890', 'Pesan uji coba Wablas');

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('wablas_msg_1122', $result['provider_id']);
    }

    public function test_gateway_manager_creates_adapters_correctly()
    {
        $fonnte = WhatsAppGatewayManager::create('fonnte');
        $this->assertInstanceOf(FonnteAdapter::class, $fonnte);

        $wablas = WhatsAppGatewayManager::create('wablas');
        $this->assertInstanceOf(WablasAdapter::class, $wablas);

        $log = WhatsAppGatewayManager::create('log');
        $this->assertInstanceOf(LogAdapter::class, $log);
    }

    public function test_attendance_notification_skipped_when_no_parent_phone()
    {
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 1', 'jumlah_siswa' => 30]);
        $siswa = Siswa::create([
            'user_id'  => User::factory()->create()->id,
            'kelas_id' => $kelas->id,
            'nis'      => '99112233',
            'nama'     => 'Budi Siswa',
        ]);

        $absensi = Absensi::create([
            'siswa_id'  => $siswa->id,
            'tanggal'   => now()->toDateString(),
            'jam_masuk' => '07:05:00',
            'status'    => 'hadir',
        ]);

        $service = new WhatsAppNotificationService();
        $result = $service->sendAttendanceNotification($siswa, $absensi, 'masuk');

        $this->assertFalse($result['success']);
        $this->assertEquals('skipped', $result['status']);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'absensi_id' => $absensi->id,
            'status'     => 'skipped',
        ]);
    }

    public function test_attendance_notification_idempotency_prevents_duplicate()
    {
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 1', 'jumlah_siswa' => 30]);
        $siswa = Siswa::create([
            'user_id'  => User::factory()->create()->id,
            'kelas_id' => $kelas->id,
            'nis'      => '99112244',
            'nama'     => 'Siti Siswa',
        ]);

        // Link parent with phone
        $parentUser = User::factory()->create(['role' => 'orangtua']);
        $ortu = OrangTua::create([
            'user_id'  => $parentUser->id,
            'siswa_id' => $siswa->id,
            'nama'     => 'Pak Hendra',
            'telepon'  => '081298765432',
        ]);
        $siswa->orangtua()->attach($ortu->id);

        $absensi = Absensi::create([
            'siswa_id'  => $siswa->id,
            'tanggal'   => now()->toDateString(),
            'jam_masuk' => '07:05:00',
            'status'    => 'hadir',
        ]);

        // Pre-create an already sent log
        WhatsappNotificationLog::create([
            'absensi_id'   => $absensi->id,
            'siswa_id'     => $siswa->id,
            'tipe'         => 'masuk',
            'nomor_tujuan' => '6281298765432',
            'pesan'        => 'Notifikasi hadir',
            'status'       => 'sent',
            'provider'     => 'log',
            'sent_at'      => now(),
        ]);

        $service = new WhatsAppNotificationService();
        $result = $service->sendAttendanceNotification($siswa, $absensi, 'masuk');

        $this->assertTrue($result['success']);
        $this->assertEquals('already_sent', $result['status']);
        // Verify only 1 log row exists (no duplicate)
        $this->assertEquals(1, WhatsappNotificationLog::where('absensi_id', $absensi->id)->count());
    }

    public function test_admin_can_access_whatsapp_logs_and_retry_failed_message()
    {
        config(['services.whatsapp.provider' => 'log']);
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '10 PPLG 2', 'jumlah_siswa' => 30]);
        $siswa = Siswa::create([
            'user_id'  => User::factory()->create()->id,
            'kelas_id' => $kelas->id,
            'nis'      => '99112255',
            'nama'     => 'Doni Siswa',
        ]);
        $parentUser2 = User::factory()->create(['role' => 'orangtua']);
        $ortu = OrangTua::create([
            'user_id'  => $parentUser2->id,
            'siswa_id' => $siswa->id,
            'nama'     => 'Ibu Doni',
            'telepon'  => '081233445566',
        ]);
        $siswa->orangtua()->attach($ortu->id);

        $absensi = Absensi::create([
            'siswa_id'  => $siswa->id,
            'tanggal'   => now()->toDateString(),
            'jam_masuk' => '07:10:00',
            'status'    => 'hadir',
        ]);

        $failedLog = WhatsappNotificationLog::create([
            'absensi_id'    => $absensi->id,
            'siswa_id'      => $siswa->id,
            'tipe'          => 'masuk',
            'nomor_tujuan'  => '6281233445566',
            'pesan'         => 'Presensi gagal terkirim',
            'status'        => 'failed',
            'provider'      => 'fonnte',
            'error_message' => 'Connection timeout',
        ]);

        $this->actingAs($admin);

        // View dashboard
        $response = $this->get(route('admin.whatsapp.logs'));
        $response->assertOk();
        $response->assertSee('Monitoring WhatsApp Gateway');
        $response->assertSee('Doni Siswa');

        // Retry sending
        $retryResponse = $this->post(route('admin.whatsapp.retry', $failedLog->id));
        $retryResponse->assertRedirect();
        $retryResponse->assertSessionHas('success');
    }
}
