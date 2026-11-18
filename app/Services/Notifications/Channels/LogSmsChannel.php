<?php

namespace App\Services\Notifications\Channels;

use App\Contracts\SmsChannelInterface;
use Illuminate\Support\Facades\Log;

class LogSmsChannel implements SmsChannelInterface
{
    /**
     * Send an SMS message by logging it to the application log.
     */
    public function send(string $to, string $message): bool
    {
        Log::info('[SMS DRIVER: LOG] Dispatching SMS', [
            'to' => $to,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);

        return true;
    }
}
