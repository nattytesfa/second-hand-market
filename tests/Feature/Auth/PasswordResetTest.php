<?php

namespace Tests\Feature\Auth;

use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_a_reset_code_can_be_requested_with_a_phone_number(): void
    {
        $user = User::factory()->create(['phone' => '0912345678']);

        $this->post('/forgot-password', ['phone' => '+251912345678'])
            ->assertRedirect(route('password.reset.create', absolute: false));

        $this->assertNotNull($this->sms->codeFor($user->phone));
        $this->assertSame('0912345678', session('password_reset_phone'));
        $this->assertSame('reset', PhoneVerification::wherePhone($user->phone)->firstOrFail()->purpose);
        $this->get('/reset-password')->assertOk();
    }

    public function test_unknown_phone_returns_the_same_response_without_sending_an_sms(): void
    {
        $this->post('/forgot-password', ['phone' => '0999999999'])
            ->assertRedirect(route('password.reset.create', absolute: false))
            ->assertSessionHas('status', 'If the number is registered, we sent a verification code to it.');

        $this->assertCount(0, $this->sms->messages);
        $this->assertCount(0, PhoneVerification::all());
    }

    public function test_password_can_be_reset_with_a_valid_code(): void
    {
        $user = User::factory()->create();
        $service = app(PhoneVerificationService::class);

        $this->post('/forgot-password', ['phone' => $user->phone]);
        $code = $this->sms->codeFor($user->phone);

        $this->post('/reset-password', [
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login', absolute: false));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse($service->verifyPasswordResetCode($user->phone, $code));
        $this->assertNull(session('password_reset_phone'));

        $this->post('/login', ['phone' => $user->phone, 'password' => 'new-password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertNotNull($service);
    }

    public function test_wrong_code_does_not_reset_the_password(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['phone' => $user->phone]);
        $correct = $this->sms->codeFor($user->phone);
        $wrongCode = $correct === '000000' ? '111111' : '000000';

        $this->post('/reset-password', [
            'code' => $wrongCode,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_expired_reset_code_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['phone' => $user->phone]);
        $code = $this->sms->codeFor($user->phone);
        PhoneVerification::wherePhone($user->phone)->update(['expires_at' => now()->subSecond()]);

        $this->post('/reset-password', [
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_attempts_are_limited(): void
    {
        config()->set('otp.max_attempts', 2);

        $user = User::factory()->create();
        $this->post('/forgot-password', ['phone' => $user->phone]);
        $code = $this->sms->codeFor($user->phone);
        $wrongCode = $code === '000000' ? '111111' : '000000';

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->post('/reset-password', [
                'code' => $wrongCode,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertSessionHasErrors('code');
        }

        $this->post('/reset-password', [
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_resend_requests_are_throttled(): void
    {
        $user = User::factory()->create();
        $this->post('/forgot-password', ['phone' => $user->phone]);
        $code = $this->sms->codeFor($user->phone);
        $messageCount = count($this->sms->messagesFor($user->phone));

        $this->post('/reset-password/resend')->assertSessionHas('status');

        $this->assertCount($messageCount, $this->sms->messagesFor($user->phone));
        $this->assertSame($code, $this->sms->codeFor($user->phone));
    }

    public function test_reset_form_requires_a_requested_code(): void
    {
        $this->get('/reset-password')->assertRedirect(route('password.request', absolute: false));
    }
}
