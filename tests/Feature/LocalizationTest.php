<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_is_the_default_language(): void
    {
        $this->get('/')->assertOk();

        $this->assertSame('en', app()->getLocale());
        $this->get('/')->assertOk()->assertSee('English');
        $this->get('/login')->assertOk()->assertSee('Log in')->assertDontSee('English');
    }

    public function test_amharic_can_be_selected_and_rendered(): void
    {
        $this->post('/locale', ['locale' => 'am'])->assertRedirect('/');

        $this->assertSame('am', session('locale'));
        $this->assertSame('am', app()->getLocale());

        $this->get('/login')
            ->assertOk()
            ->assertSee('ግባ')
            ->assertSee('lang="am"', false)
            ->assertSee('font-ethiopic', false);
    }

    public function test_afaan_oromo_can_be_selected_and_rendered(): void
    {
        $this->post('/locale', ['locale' => 'om']);

        $this->assertSame('om', session('locale'));
        $this->assertSame('om', app()->getLocale());

        $this->get('/forgot-password')->assertOk()->assertSee('Koodii ergi');
        $this->get('/')->assertSee('Afaan Oromoo');
        $this->get('/login')->assertDontSee('Afaan Oromoo');
    }

    public function test_selected_language_persists_across_requests(): void
    {
        $this->withSession(['locale' => 'am'])->get('/login')->assertSee('ግባ');
        $this->get('/register')->assertSee('ተመዝገብ');
        $this->get('/verify-phone')->assertRedirect(route('login', absolute: false));
    }

    public function test_unsupported_session_locale_falls_back_to_english(): void
    {
        $this->withSession(['locale' => 'fr'])->get('/login')
            ->assertOk()
            ->assertSee('Log in');

        $this->assertSame('en', app()->getLocale());
        $this->assertSame('en', session('locale'));
    }

    public function test_unsupported_switcher_submission_is_rejected(): void
    {
        $this->post('/locale', ['locale' => 'xx'])->assertSessionHasErrors('locale');

        $this->assertSame('en', app()->getLocale());
        $this->assertSame('en', session('locale'));
    }

    public function test_translation_keys_resolve_in_all_three_languages(): void
    {
        $this->assertSame('Home', __('navigation.home'));
        $this->assertSame('Password reset successfully. Please log in.', __('passwords.status_success'));

        app()->setLocale('am');
        $amharic = __('navigation.home');
        $this->assertSame('ዋና ገጽ', $amharic);
        $this->assertTrue(mb_check_encoding($amharic, 'UTF-8'));
        $this->assertSame('ተረጋግጧል', __('profile.verified'));

        app()->setLocale('om');
        $this->assertSame('Fuula Duraa', __('navigation.home'));
        $this->assertSame('Mirkaana’e', __('profile.verified'));

        app()->setLocale('en');
    }

    public function test_switcher_is_on_home_for_guests_and_profile_settings_for_users(): void
    {
        $this->get('/')
            ->assertSee('አማርኛ')
            ->assertSee('Afaan Oromoo');

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('አማርኛ');

        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertDontSee('አማርኛ');

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('አማርኛ');

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('አማርኛ')
            ->assertSee('Afaan Oromoo')
            ->assertSee('Settings');
    }

    public function test_switching_language_returns_to_the_current_page(): void
    {
        $this->from('/profile')->post('/locale', ['locale' => 'am'])->assertRedirect('/profile');

        $this->assertSame('am', session('locale'));
    }

    public function test_user_generated_content_is_not_translated(): void
    {
        $user = User::factory()->create(['name' => 'Ada Tesfaye']);

        $this->actingAs($user)->withSession(['locale' => 'am'])->get('/profile')
            ->assertOk()
            ->assertSee('Ada Tesfaye');
    }
}
