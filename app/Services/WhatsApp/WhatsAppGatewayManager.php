<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Adapters\FonnteAdapter;
use App\Services\WhatsApp\Adapters\LogAdapter;
use App\Services\WhatsApp\Adapters\WablasAdapter;
use InvalidArgumentException;

class WhatsAppGatewayManager
{
    /**
     * Resolve the active WhatsApp gateway adapter
     */
    public static function create(?string $provider = null): WhatsAppGatewayInterface
    {
        $provider = $provider ?? config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'fonnte'));
        $token = config('services.whatsapp.token', env('WHATSAPP_API_TOKEN', ''));
        $enabled = config('services.whatsapp.enabled', env('WHATSAPP_ENABLED', false));

        if (strtolower($provider) === 'wablas') {
            return new WablasAdapter();
        }

        if (strtolower($provider) === 'fonnte') {
            return new FonnteAdapter();
        }

        return new LogAdapter();
    }
}
