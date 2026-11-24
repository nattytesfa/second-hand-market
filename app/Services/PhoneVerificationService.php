<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PhoneVerificationService
{
    public function send(string $phone): string
    {
        return $this->createCode($phone, 'verify', 'Phone verification code');
    }

    public function verify(string $phone, string $code): bool
    {
        return $this->checkCode($phone, $code, 'verify');
    }

    public function debugCode(string $phone): ?string
    {
        return $this->debug($phone, 'verify');
    }

    public function forget(string $phone): void
    {
        $this->forgetCode($phone, 'verify');
    }

    public function sendPasswordResetCode(string $phone): string
    {
        return $this->createCode($phone, 'reset', 'Password reset code');
    }

    public function verifyPasswordResetCode(string $phone, string $code): bool
    {
        return $this->checkCode($phone, $code, 'reset');
    }

    public function debugPasswordResetCode(string $phone): ?string
    {
        return $this->debug($phone, 'reset');
    }

    public function forgetPasswordResetCode(string $phone): void
    {
        $this->forgetCode($phone, 'reset');
    }

    private function createCode(string $phone, string $purpose, string $logLabel): string
    {
        $length = config('otp.length');
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $ttl = now()->addMinutes(config('otp.expiry_minutes'));

        Cache::put($this->key($phone, $purpose), [
            'code' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->timestamp + (config('otp.expiry_minutes') * 60),
        ], $ttl);

        Log::info("{$logLabel} for {$phone}: {$code}");

        if (app()->environment(['local', 'testing'])) {
            Cache::put($this->debugKey($phone, $purpose), $code, $ttl);
        }

        return $code;
    }

    private function checkCode(string $phone, string $code, string $purpose): bool
    {
        $key = $this->key($phone, $purpose);
        $data = Cache::get($key);

        if (! $data || now()->timestamp > $data['expires_at'] || $data['attempts'] >= config('otp.max_attempts')) {
            $this->forgetCode($phone, $purpose);

            return false;
        }

        $data['attempts']++;

        if (! Hash::check($code, $data['code'])) {
            Cache::put($key, $data, now()->addMinutes(config('otp.expiry_minutes')));

            return false;
        }

        $this->forgetCode($phone, $purpose);

        return true;
    }

    private function debug(string $phone, string $purpose): ?string
    {
        return app()->environment(['local', 'testing'])
            ? Cache::get($this->debugKey($phone, $purpose))
            : null;
    }

    private function forgetCode(string $phone, string $purpose): void
    {
        Cache::forget($this->key($phone, $purpose));
        Cache::forget($this->debugKey($phone, $purpose));
    }

    private function key(string $phone, string $purpose): string
    {
        return $purpose.'-otp:'.sha1($phone);
    }

    private function debugKey(string $phone, string $purpose): string
    {
        return $purpose.'-otp-debug:'.sha1($phone);
    }
}
