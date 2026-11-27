<?php

namespace Tests\Feature\Auth;

use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Support\EthiopianPhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verify-phone')->assertOk();
    }

    public function test_an_otp_challenge_is_issued_and_recorded(): void
    {
        $user = User::factory()->unverified()->create();
        app(PhoneVerificationService::class)->send($user->phone);

        $code = $this->sms->codeFor($user->phone);
        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $record = PhoneVerification::wherePhone($user->phone)->firstOrFail();
        $this->assertSame('verify', $record->purpose);
        $this->assertFalse($record->isExpired());
        $this->assertNull($record->consumed_at);
    }

    public function test_correct_code_verifies_the_phone_number_and_cannot_be_reused(): void
    {
        $user = User::factory()->unverified()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);
        $code = $this->sms->codeFor($user->phone);

        $this->actingAs($user)->post('/verify-phone', ['code' => $code])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertNotNull(PhoneVerification::wherePhone($user->phone)->firstOrFail()->consumed_at);
        $this->assertFalse($service->verify($user->phone, $code));
    }

    public function test_wrong_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        app(PhoneVerificationService::class)->send($user->phone);
        $code = $this->sms->codeFor($user->phone);
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->actingAs($user)->post('/verify-phone', ['code' => $wrongCode])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        app(PhoneVerificationService::class)->send($user->phone);
        $code = $this->sms->codeFor($user->phone);

        PhoneVerification::wherePhone($user->phone)->update(['expires_at' => now()->subSecond()]);

        $this->actingAs($user)->post('/verify-phone', ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_verification_attempts_are_limited(): void
    {
        config()->set('otp.max_attempts', 2);

        $user = User::factory()->unverified()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);
        $code = $this->sms->codeFor($user->phone);
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->actingAs($user)->post('/verify-phone', ['code' => $wrongCode])->assertSessionHasErrors('code');
        $this->actingAs($user)->post('/verify-phone', ['code' => $wrongCode])->assertSessionHasErrors('code');
        $this->actingAs($user)->post('/verify-phone', ['code' => $code])->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
        $this->assertNotNull(PhoneVerification::wherePhone($user->phone)->firstOrFail()->consumed_at);
    }

    public function test_resend_requests_are_throttled(): void
    {
        $user = User::factory()->unverified()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);
        $code = $this->sms->codeFor($user->phone);
        $messageCount = count($this->sms->messagesFor($user->phone));

        $this->actingAs($user)->post('/verify-phone/resend')->assertSessionHasErrors('code');

        $this->assertCount($messageCount, $this->sms->messagesFor($user->phone));
        $this->assertSame($code, $this->sms->codeFor($user->phone));
        $this->assertCount(1, PhoneVerification::wherePhone($user->phone)->whereNull('consumed_at')->get());
    }

    public function test_a_new_otp_invalidates_the_previous_one(): void
    {
        $user = User::factory()->unverified()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);
        $oldCode = $this->sms->codeFor($user->phone);

        $this->travel(61)->seconds();
        $service->send(EthiopianPhone::normalize('+251'.substr($user->phone, 1)));
        $newCode = $this->sms->codeFor($user->phone);

        $this->assertNotSame($oldCode, $newCode);
        $this->assertFalse($service->verify($user->phone, $oldCode));
        $this->assertTrue($service->verify($user->phone, $newCode));
    }

    public function test_otps_for_different_purposes_are_not_interchangeable(): void
    {
        $user = User::factory()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);

        $this->travel(61)->seconds();
        $service->sendPasswordResetCode($user->phone);
        $resetCode = $this->sms->codeFor($user->phone);

        $this->assertFalse($service->verify($user->phone, $resetCode));
        $this->assertTrue($service->verifyPasswordResetCode($user->phone, $resetCode));
    }

    public function test_unverified_user_cannot_access_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('otp.create', absolute: false));
    }

    public function test_a_new_code_can_be_requested_after_the_throttle_window(): void
    {
        $user = User::factory()->unverified()->create();
        $service = app(PhoneVerificationService::class);
        $service->send($user->phone);
        $firstCode = $this->sms->codeFor($user->phone);

        $this->travel(61)->seconds();
        $this->actingAs($user)->post('/verify-phone/resend')->assertStatus(302);

        $this->assertNotSame($firstCode, $this->sms->codeFor($user->phone));
        $this->assertNotNull($this->sms->codeFor($user->phone));
    }
}
