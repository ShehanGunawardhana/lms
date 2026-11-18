<?php

namespace App\Contracts;

interface SmsChannelInterface
{
    /**
     * Send an SMS message to the given destination phone number.
     */
    public function send(string $to, string $message): bool;
}
