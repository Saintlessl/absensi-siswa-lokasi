<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppOtpService
{
    protected string $token;
    protected string $baseUrl;

    public function __construct(?string $token = null, ?string $baseUrl = null)
    {
        $this->token = $token ?? (string) config('services.fonnte.token', env('FONNTE_TOKEN', env('WHATSAPP_API_TOKEN', '')));
        $this->baseUrl = $baseUrl ?? (string) config('services.fonnte.base_url', env('FONNTE_BASE_URL', 'https://api.fonnte.com/send'));
    }

    /**
     * Check if Fonnte token is configured.
     */
    public function isConfigured(): bool
    {
        return !empty(trim($this->token));
    }

    /**
     * Get configured token (masked for safe display in UI/settings).
     */
    public function getMaskedToken(): string
    {
        if (empty($this->token)) {
            return 'BELUM DIKONFIGURASI';
        }
        $len = strlen($this->token);
        if ($len <= 6) {
            return str_repeat('*', $len);
        }
        return substr($this->token, 0, 4) . str_repeat('*', $len - 6) . substr($this->token, -2);
    }

    /**
     * Send 6-digit OTP code to student WhatsApp number via Fonnte Free API.
     *
     * @param string $phoneNumber Target phone number
     * @param string $otp 6-digit OTP
     * @return array ['success' => bool, 'message' => string, 'provider_id' => ?string, 'error' => ?string]
     */
    public function sendOtp(string $phoneNumber, string $otp): array
    {
        if (!$this->isConfigured()) {
            Log::warning('[WhatsAppOtpService] Pengiriman OTP dibatalkan: FONNTE_TOKEN belum diisi di environment.');
            return [
                'success'     => false,
                'message'     => 'Layanan WhatsApp Gateway Fonnte belum dikonfigurasi oleh Administrator. Silakan hubungi admin sekolah.',
                'provider_id' => null,
                'error'       => 'FONNTE_TOKEN_NOT_CONFIGURED',
            ];
        }

        // Format message matching official prompt template strictly
        $message = "MY SKANIC - VERIFIKASI PASSWORD\n\n"
            . "Halo, kode verifikasi untuk mengatur ulang password akun MySkanic kamu adalah:\n\n"
            . "{$otp}\n\n"
            . "Kode ini berlaku selama 5 menit.\n\n"
            . "Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengaku sebagai Admin MySkanic.\n\n"
            . "Jika kamu tidak meminta pengaturan ulang password, abaikan pesan ini.";

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->post($this->baseUrl, [
                    'target'      => $phoneNumber,
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

            $body = $response->json() ?? [];
            $status = $body['status'] ?? false;
            $isSuccess = $response->successful() && ($status === true || $status === 'true');

            if ($isSuccess) {
                $providerId = $body['id'] ?? ($body['process_id'] ?? null);
                if (is_array($providerId)) {
                    $providerId = implode(',', $providerId);
                }

                // Log success WITHOUT logging OTP
                Log::info('[WhatsAppOtpService] OTP WhatsApp berhasil dikirim. Provider ID: ' . ($providerId ?: '-'));

                return [
                    'success'     => true,
                    'message'     => 'Kode OTP berhasil dikirimkan ke nomor WhatsApp Anda.',
                    'provider_id' => $providerId ? (string) $providerId : null,
                    'error'       => null,
                ];
            }

            $reason = $body['reason'] ?? ($body['message'] ?? 'Gateway Fonnte merespons gagal');
            Log::error("[WhatsAppOtpService Error] Fonnte API failure: {$reason}");

            return [
                'success'     => false,
                'message'     => 'Pengiriman pesan WhatsApp gagal (' . $reason . '). Silakan coba lagi beberapa saat.',
                'provider_id' => null,
                'error'       => (string) $reason,
            ];
        } catch (\Throwable $e) {
            // Log connection error WITHOUT logging OTP
            Log::error("[WhatsAppOtpService Exception] Gagal terhubung ke Fonnte: {$e->getMessage()}");

            return [
                'success'     => false,
                'message'     => 'Terjadi gangguan jaringan saat menghubungi gateway WhatsApp. Silakan coba lagi beberapa saat.',
                'provider_id' => null,
                'error'       => $e->getMessage(),
            ];
        }
    }
}
