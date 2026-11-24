<?php

namespace App\Services;

use App\Exceptions\SmsDeliveryException;
use App\Services\Contracts\OtpProvider;
use App\Support\EthiopianPhone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AfroMessage OTP provider using the official challenge/verify endpoints.
 *
 * Challenge: GET https://api.afromessage.com/api/challenge
 * Verify:    GET https://api.afromessage.com/api/verify
 * Auth:      Authorization: Bearer <token>
 *
 * @see https://www.afromessage.com/developers
 */
class AfroMessageOtpProvider implements OtpProvider
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function challenge(string $phone, array $options = []): array
    {
        $config = $this->config();

        $query = array_filter([
            'to' => $this->toInternational($phone),
            'len' => (int) config('otp.length', 6),
            'ttl' => max(0, (int) config('otp.expiry_minutes', 5)) * 60,
            't' => 0, // numeric OTP
            'pr' => $options['prefix'] ?? 'Your Adama Market code is',
            'from' => $config['identifier_id'] ?: null,
            'sender' => $config['sender'] ?: null,
            'callback' => $config['callback'] ?: null,
        ], fn ($value) => $value !== null && $value !== '');

        $response = $this->request($config, 'challenge', $query);

        return is_array($response) ? $response : [];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function verify(string $phone, string $code, array $options = []): bool
    {
        $config = $this->config();

        $response = $this->request($config, 'verify', [
            'to' => $this->toInternational($phone),
            'code' => $code,
        ]);

        // AfroMessage verification responses vary by SDK/version; accept clear
        // success signals and reject everything else.
        if (($response['acknowledge'] ?? null) === 'success') {
            return true;
        }

        $responseCode = data_get($response, 'response.code');

        return in_array($responseCode, ['200', 200, 'success', 'Success'], true);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function request(array $config, string $path, array $query): array
    {
        $token = $config['token'];
        $sender = $config['sender'];

        if (! $token || ! $sender) {
            throw new SmsDeliveryException(
                'AfroMessage delivery is not configured. Set AFROSMS_TOKEN and AFROSMS_SENDER_NAME.'
            );
        }

        $endpoint = rtrim((string) ($config['api_base'] ?? 'https://api.afromessage.com/api'), '/').'/'.$path;

        try {
            $httpResponse = Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->get($endpoint, $query);
        } catch (ConnectionException $exception) {
            Log::warning('AfroMessage OTP request could not be completed.', ['path' => $path]);

            throw new SmsDeliveryException('AfroMessage delivery failed.', previous: $exception);
        }

        $json = $httpResponse->json();
        $payload = is_array($json) ? $json : [];

        if (! $httpResponse->successful()) {
            Log::warning('AfroMessage OTP request was rejected.', [
                'path' => $path,
                'status' => $httpResponse->status(),
                'acknowledge' => $payload['acknowledge'] ?? null,
            ]);

            throw new SmsDeliveryException('AfroMessage delivery failed.');
        }

        if (($payload['acknowledge'] ?? null) === 'error') {
            Log::warning('AfroMessage OTP request returned an error.', [
                'path' => $path,
                'status' => $httpResponse->status(),
                'acknowledge' => $payload['acknowledge'] ?? null,
            ]);

            throw new SmsDeliveryException('AfroMessage delivery failed.');
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function config(): array
    {
        return config('services.sms.afrosms', []);
    }

    private function toInternational(string $phone): string
    {
        $normalized = EthiopianPhone::normalize($phone) ?? $phone;

        return str_starts_with($normalized, '0')
            ? '251'.substr($normalized, 1)
            : $normalized;
    }
}
