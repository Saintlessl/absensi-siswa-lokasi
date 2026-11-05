<?php

namespace App\Services\WhatsApp\Adapters;

use App\Services\WhatsApp\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteAdapter implements WhatsAppGatewayInterface
{
    protected string $token;
    protected string $apiUrl;

    public function __construct(?string $token = null, ?string $apiUrl = null)
    {
        $this->token = $token ?? config('services.whatsapp.token', env('WHATSAPP_API_TOKEN', ''));
        $this->apiUrl = $apiUrl ?? config('services.whatsapp.url', env('WHATSAPP_API_URL', 'https://api.fonnte.com/send'));
    }

    public function send(string $phoneNumber, string $message, array $metadata = []): array
    {
        if (empty($this->token)) {
            return [
                'success'     => false,
                'status'      => 'failed',
                'provider_id' => null,
                'response'    => ['error' => 'WHATSAPP_API_TOKEN belum dikonfigurasi.'],
                'error'       => 'WHATSAPP_API_TOKEN belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->post($this->apiUrl, [
                    'target'      => $phoneNumber,
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

            $body = $response->json() ?? [];
            $isSuccess = $response->successful() && (($body['status'] ?? false) === true || ($body['status'] ?? false) === 'true');
            $providerId = $body['id'] ?? ($body['process_id'] ?? null);

            return [
                'success'     => $isSuccess,
                'status'      => $isSuccess ? 'sent' : 'failed',
                'provider_id' => $providerId ? (string) $providerId : null,
                'response'    => $body,
                'error'       => $isSuccess ? null : ($body['reason'] ?? ($body['message'] ?? 'Gateway Fonnte merespons gagal')),
            ];
        } catch (\Throwable $e) {
            Log::error("[FonnteAdapter Error] {$e->getMessage()}");
            return [
                'success'     => false,
                'status'      => 'failed',
                'provider_id' => null,
                'response'    => ['exception' => $e->getMessage()],
                'error'       => $e->getMessage(),
            ];
        }
    }

    public function getProviderName(): string
    {
        return 'fonnte';
    }
}
