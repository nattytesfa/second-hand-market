<?php

namespace App\Services\Contracts;

interface OtpProvider
{
    /**
     * Send an OTP challenge to the given phone number.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed> Provider response metadata (may be empty).
     */
    public function challenge(string $phone, array $options = []): array;

    /**
     * Verify an OTP code for the given phone number.
     */
    /**
     * Verify an OTP code for the given phone number.
     *
     * @param  array<string, mixed>  $options
     */
    public function verify(string $phone, string $code, array $options = []): bool;
}
