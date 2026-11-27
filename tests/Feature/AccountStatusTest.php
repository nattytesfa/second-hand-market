<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_users_are_blocked_from_protected_routes(): void
    {
        $user = User::factory()->suspended()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login', absolute: false));
        $this->actingAs($user)->get('/profile')->assertRedirect(route('login', absolute: false));
        $this->actingAs($user)->get('/verify-phone')->assertRedirect(route('login', absolute: false));
        $this->assertGuest();
    }

    public function test_banned_users_are_blocked_from_protected_routes(): void
    {
        $user = User::factory()->banned()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login', absolute: false));
        $this->actingAs($user)->get('/profile')->assertRedirect(route('login', absolute: false));
        $this->actingAs($user)->get('/verify-phone')->assertRedirect(route('login', absolute: false));
        $this->assertGuest();
    }

    public function test_active_users_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }
}
