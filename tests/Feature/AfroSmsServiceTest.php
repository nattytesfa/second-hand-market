<?php

namespace Tests\Feature;

use App\Services\AfroSmsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AfroSmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.sms.afrosms.token', 'test-token');
        config()->set('services.sms.afrosms.identifier_id', 'IDENTIFIER-1');
        config()->set('services.sms.afrosms.sender', 'ADAMA');
        config()->set('services.sms.afrosms.endpoint', 'https://api.afromessage.com/api/send');
        config()->set('services.sms.afrosms.callback', null);
    }

    public function test_it_posts_to_the_documented_endpoint_with_bearer_auth(): void
    {
        Http::fake([
            'api.afromessage.com/api/send' => Http::response([
                'acknowledge' => 'success',
                'response' => ['status' => 'Send in progress...'],
            ], 200),
        ]);

        $code = '123456';
        app(AfroSmsService::class)->send('0912345678', "Your verification code is {$code}.");

        Http::assertSent(function (Request $request) use ($code): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.afromessage.com/api/send'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request['from'] === 'IDENTIFIER-1'
                && $request['sender'] === 'ADAMA'
                && $request['to'] === '251912345678'
                && $request['message'] === "Your verification code is {$code}.";
        });
    }

    public function test_it_omits_optional_fields_when_not_configured(): void
    {
        config()->set('services.sms.afrosms.identifier_id', null);
        config()->set('services.sms.afrosms.callback', null);

        Http::fake([
            'api.afromessage.com/api/send' => Http::response(['acknowledge' => 'success'], 200),
        ]);

        app(AfroSmsService::class)->send('0912345678', 'Your verification code is 123456.');

        Http::assertSent(function (Request $request): bool {
            return ! array_key_exists('from', $request->data())
                && ! array_key_exists('callback', $request->data());
        });
    }

    public function test_it_includes_a_configured_callback_url(): void
    {
        config()->set('services.sms.afrosms.callback', 'https://example.test/sms-status');

        Http::fake([
            'api.afromessage.com/api/send' => Http::response(['acknowledge' => 'success'], 200),
        ]);

        app(AfroSmsService::class)->send('0912345678', 'Your verification code is 123456.');

        Http::assertSent(fn (Request $request): bool => $request['callback'] === 'https://example.test/sms-status');
    }

    public function test_it_rejects_an_error_acknowledgement_without_leaking_the_otp(): void
    {
        $code = '123456';
        Log::spy();

        Http::fake([
            'api.afromessage.com/api/send' => Http::response([
                'acknowledge' => 'error',
                'response' => ['errors' => "Unable to send: your code is {$code}."],
            ], 200),
        ]);

        try {
            app(AfroSmsService::class)->send('0912345678', "Your verification code is {$code}.");
            $this->fail('An error acknowledgement should raise an exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString($code, $exception->getMessage());
        }

        Log::shouldHaveReceived('warning')->with(
            'AfroMessage rejected a message.',
            Mockery::on(fn (array $context): bool => ! str_contains(json_encode($context), $code))
        );
    }

    public function test_it_requires_credentials_before_making_a_request(): void
    {
        config()->set('services.sms.afrosms.token', null);
        config()->set('services.sms.afrosms.sender', null);
        Http::fake();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not configured');

        app(AfroSmsService::class)->send('0912345678', 'Your verification code is 123456.');
    }
}
