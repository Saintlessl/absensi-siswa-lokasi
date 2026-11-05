<?php

namespace App\Jobs;

use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\WhatsappNotificationLog;
use App\Services\WhatsApp\WhatsAppGatewayManager;
use App\Services\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public int $siswaId;
    public int $absensiId;
    public string $tipe;

    /**
     * Create a new job instance.
     */
    public function __construct(int $siswaId, int $absensiId, string $tipe = 'masuk')
    {
        $this->siswaId = $siswaId;
        $this->absensiId = $absensiId;
        $this->tipe = $tipe;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppNotificationService $service): void
    {
        $siswa = Siswa::find($this->siswaId);
        $absensi = Absensi::find($this->absensiId);

        if (!$siswa || !$absensi) {
            Log::warning("[WhatsApp Job] Siswa ID {$this->siswaId} or Absensi ID {$this->absensiId} not found. Skipping.");
            return;
        }

        // Delegate to service which handles phone resolution, idempotency check, adapter sending, and logging
        $service->sendAttendanceNotification($siswa, $absensi, $this->tipe);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[WhatsApp Job Failed] Siswa {$this->siswaId}, Absensi {$this->absensiId}: " . $exception->getMessage());

        WhatsappNotificationLog::where('absensi_id', $this->absensiId)
            ->where('tipe', $this->tipe)
            ->where('status', 'pending')
            ->update([
                'status'        => 'failed',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
            ]);
    }
}
