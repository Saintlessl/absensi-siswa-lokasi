<?php

namespace App\Services\WhatsApp;

interface WhatsAppGatewayInterface
{
    /**
     * Send a WhatsApp message to a phone number.
     *
     * @param string $phoneNumber Normalized destination number (e.g. 628123456789)
     * @param string $message Formatted text message
     * @param array $metadata Optional metadata (event, student_id, etc.)
     * @return array Standardized result ['success' => bool, 'status' => string, 'provider_id' => ?string, 'response' => mixed, 'error' => ?string]
     */
    public function send(string $phoneNumber, string $message, array $metadata = []): array;

    /**
     * Get the provider identifier name (e.g. 'fonnte', 'wablas', 'log')
     */
    public function getProviderName(): string;
}
