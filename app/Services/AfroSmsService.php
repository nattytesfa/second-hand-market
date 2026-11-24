<?php

namespace App\Services;

use App\Exceptions\SmsDeliveryException;
use App\Services\Contracts\SmsService;
use App\Support\EthiopianPhone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adapter for the official AfroMessage "Send SMS" API.
 *
 * Endpoint: POST https://api.afromessage.com/api/send
 * Auth: Authorization: Bearer <token>
 * Body: { from?, sender, to, message, callback? }
 * Success: HTTP 200 and JSON "acknowledge" === "success".
 *
 * See https://www.afromessage.com/developers#dev-getsend
 *
 * Delivery is opt-in via SMS_PROVIDER=afrosms and requires credentials.
 * Response bodies are never logged because the API echoes the original
 * message (which contains the OTP).
 */
class AfroSmsService implements SmsService
{
    public function send(string $phone, string $message): void
    {
        $config = config('services.sms.afrosms');
        $token = $config['token'];
        $sender = $config['sender'];

        if (! $token || ! $sender) {
            throw new SmsDeliveryException(
                'AfroMessage delivery is not configured. Set AFROSMS_TOKEN and AFROSMS_SENDER_NAME.'
            );
        }

        $payload = [
            'sender' => $sender,
            'to' => $this->toInternational($phone),
            'message' => $message,
        ];

        if (! empty($config['identifier_id'])) {
            $payload['from'] = $config['identifier_id'];
        }

        if (! empty($config['callback'])) {
            $payload['callback'] = $config['callback'];
        }

        try {
            $response = Http::withToken($token)
                ->asJson()
                ->acceptJson()
                ->timeout(10)
                ->post($config['endpoint'], $payload);
        } catch (ConnectionException $exception) {
            // Connection errors contain no message body, but log only a generic notice.
            Log::warning('AfroMessage request could not be completed.');

            throw new SmsDeliveryException('AfroMessage delivery failed.', previous: $exception);
        }

        if (! $response->successful() || $response->json('acknowledge') !== 'success') {
            Log::warning('AfroMessage rejected a message.', [
                'status' => $response->status(),
                'acknowledge' => $response->json('acknowledge'),
            ]);

            throw new SmsDeliveryException('AfroMessage delivery failed.');
        }
    }

    /**
     * AfroMessage expects the recipient in international form (2519…).
     */
    private function toInternational(string $phone): string
    {
        $normalized = EthiopianPhone::normalize($phone) ?? $phone;

        return str_starts_with($normalized, '0')
            ? '251'.substr($normalized, 1)
            : $normalized;
    }
}
