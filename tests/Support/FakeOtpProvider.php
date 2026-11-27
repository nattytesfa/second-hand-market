<?php

namespace Tests\Support;

use App\Services\Contracts\OtpProvider;

class FakeOtpProvider implements OtpProvider
{
    /** @var array<string, string> */
    public array $codes = [];

    /** @var list<array{phone: string, message: string}> */
    public array $messages = [];

    /** @var list<array{phone: string, options: array<string, mixed>}> */
    public array $challenges = [];

    public bool $shouldFail = false;

    public function challenge(string $phone, array $options = []): array
    {
        if ($this->shouldFail) {
            throw new \App\Exceptions\SmsDeliveryException('AfroMessage delivery failed.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $purpose = $options['purpose'] ?? 'default';
        $this->codes[$phone.':'.$purpose] = $code;
        $this->codes[$phone] = $code; // convenience for codeFor()
        $this->messages[] = ['phone' => $phone, 'message' => "Your code is {$code}"];
        $this->challenges[] = ['phone' => $phone, 'options' => $options];

        return ['code' => $code];
    }

    public function verify(string $phone, string $code, array $options = []): bool
    {
        $purpose = $options['purpose'] ?? null;

        if (is_string($purpose) && $purpose !== '') {
            return ($this->codes[$phone.':'.$purpose] ?? null) === $code;
        }

        return ($this->codes[$phone] ?? null) === $code;
    }

    public function codeFor(string $phone, ?string $purpose = null): ?string
    {
        if (is_string($purpose) && $purpose !== '') {
            return $this->codes[$phone.':'.$purpose] ?? null;
        }

        return $this->codes[$phone] ?? null;
    }

    /**
     * @return list<array{phone: string, message: string}>
     */
    public function messagesFor(string $phone): array
    {
        return array_values(array_filter(
            $this->messages,
            fn (array $message): bool => $message['phone'] === $phone
        ));
    }

    public function reset(): void
    {
        $this->codes = [];
        $this->messages = [];
        $this->challenges = [];
        $this->shouldFail = false;
    }
}
