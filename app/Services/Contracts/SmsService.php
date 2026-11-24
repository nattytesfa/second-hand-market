<?php

namespace App\Services\Contracts;

interface SmsService
{
    /**
     * Deliver an SMS to the given (already normalized) phone number.
     *
     * Implementations must never include the OTP in application logs.
     */
    public function send(string $phone, string $message): void;
}
