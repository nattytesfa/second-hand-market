<?php

namespace Tests\Feature\Auth;

use App\Exceptions\SmsDeliveryException;
use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\Contracts\OtpProvider;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakeOtpProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register_with_a_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('otp.create', absolute: false));

        $user = User::wherePhone('0912345678')->firstOrFail();

        $this->assertNull($user->phone_verified_at);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($this->sms->codeFor($user->phone));

        $verification = PhoneVerification::wherePhone($user->phone)->firstOrFail();
        $this->assertSame('verify', $verification->purpose);
        $this->assertNull($verification->consumed_at);
    }

    public function test_international_phone_numbers_are_normalized_on_registration(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'phone' => '+251 91 234 56 78',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('otp.create', absolute: false));

        $this->assertDatabaseHas('users', ['phone' => '0912345678']);
        $this->assertCount(1, User::all());
    }

    public function test_duplicate_phone_numbers_are_rejected_after_normalization(): void
    {
        User::factory()->create(['phone' => '0912345678']);

        $this->post('/register', [
            'name' => 'Another User',
            'phone' => '+251912345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
        $this->assertCount(1, User::all());
    }

    public function test_invalid_phone_numbers_are_rejected(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'phone' => '12345',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_password_confirmation_is_required(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_registration_does_not_require_or_store_an_email_address(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0912345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertArrayNotHasKey('email', User::first()->getAttributes());
    }

    public function test_phone_is_normalized_at_the_model_level(): void
    {
        $user = User::factory()->create(['phone' => '+251712345678']);

        $this->assertSame('0712345678', $user->phone);
        $this->assertSame('0712345678', $user->fresh()->phone);
    }

    public function test_failed_sms_does_not_leave_a_registered_phone_behind(): void
    {
        $failing = new FakeOtpProvider();
        $failing->shouldFail = true;
        $this->app->instance(OtpProvider::class, $failing);

        $data = [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/register', $data)->assertSessionHasErrors('phone');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['phone' => '0912345678']);
        $this->assertDatabaseMissing('phone_verifications', ['phone' => '0912345678']);

        // A retry after the provider is fixed must not report the phone as taken.
        $this->app->instance(OtpProvider::class, $this->sms);

        $this->post('/register', $data)->assertRedirect(route('otp.create', absolute: false));

        $this->assertAuthenticated();
        $this->assertCount(1, User::all());
    }

    public function test_phone_normalizer_accepts_supported_formats(): void
    {
        $service = app(PhoneVerificationService::class);

        foreach (['0912345678', '+251912345678', '251912345678', '0912345678'] as $phone) {
            $this->assertSame('0912345678', \App\Support\EthiopianPhone::normalize($phone));
        }

        $this->assertSame('0712345678', \App\Support\EthiopianPhone::normalize('+251712345678'));
        $this->assertNull(\App\Support\EthiopianPhone::normalize('+254712345678'));
        $this->assertNotNull($service);
    }
}
