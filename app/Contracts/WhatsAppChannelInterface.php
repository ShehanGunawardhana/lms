<?php

namespace App\Contracts;

interface WhatsAppChannelInterface
{
    /**
     * Send a WhatsApp message to the given destination phone number.
     *
     * @param  array<string, mixed>  $media  Optional media parameters (url, caption, type)
     */
    public function send(string $to, string $message, array $media = []): bool;
}
