<?php

namespace App\Services\Notifications\Channels;

use App\Contracts\WhatsAppChannelInterface;
use Illuminate\Support\Facades\Log;

class LogWhatsAppChannel implements WhatsAppChannelInterface
{
    /**
     * Send a WhatsApp message by logging it to the application log.
     *
     * @param  array<string, mixed>  $media
     */
    public function send(string $to, string $message, array $media = []): bool
    {
        Log::info('[WHATSAPP DRIVER: LOG] Dispatching WhatsApp message', [
            'to' => $to,
            'message' => $message,
            'media' => $media,
            'timestamp' => now()->toIso8601String(),
        ]);

        return true;
    }
}
