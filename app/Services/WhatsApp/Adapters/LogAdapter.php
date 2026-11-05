<?php

namespace App\Services\WhatsApp\Adapters;

use App\Services\WhatsApp\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Log;

class LogAdapter implements WhatsAppGatewayInterface
{
    public function send(string $phoneNumber, string $message, array $metadata = []): array
    {
        Log::info("[WhatsApp LogAdapter] Target: {$phoneNumber} | Message:\n{$message}");

        return [
            'success'     => true,
            'status'      => 'sent',
            'provider_id' => 'sim_' . uniqid(),
            'response'    => [
                'mode'   => 'simulation/log',
                'notice' => 'Terkirim via LogAdapter. Konfigurasi WHATSAPP_API_TOKEN untuk pengiriman langsung.',
                'target' => $phoneNumber,
            ],
            'error'       => null,
        ];
    }

    public function getProviderName(): string
    {
        return 'log';
    }
}
