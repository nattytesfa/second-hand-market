<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RolesAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Probe routes for middleware authorization. No admin UI is created;
        // these routes exist only inside the test process.
        Route::middleware(['web', 'admin'])->get('/_test/admin', fn () => 'admin-ok');
        Route::middleware(['web', 'superadmin'])->get('/_test/superadmin', fn () => 'superadmin-ok');
    }

    public function test_new_registrations_receive_the_user_role_even_when_role_is_submitted(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_SUPERADMIN,
            'status' => User::STATUS_BANNED,
        ]);

        $response->assertRedirect(route('otp.create', absolute: false));

        $user = User::firstOrFail();

        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
    }

    public function test_role_is_not_mass_assignable(): void
    {
        $user = User::create([
            'name' => 'Mass Assignment Test',
            'phone' => '0912345678',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
    }

    public function test_users_cannot_change_their_own_role_through_profile_updates(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $originalName = $user->name;

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Renamed User',
            'role' => User::ROLE_ADMIN,
        ])->assertSessionHasErrors('role');

        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
        $this->assertSame($originalName, $user->fresh()->name);
    }

    public function test_role_update_policy_only_allows_superadmins_to_manage_regular_roles(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();

        $this->assertTrue(Gate::forUser($user)->denies('update-role', [$user, User::ROLE_ADMIN]));
        $this->assertTrue(Gate::forUser($user)->denies('update-role', [$otherUser, User::ROLE_ADMIN]));
        $this->assertTrue(Gate::forUser($admin)->denies('update-role', [$admin, User::ROLE_SUPERADMIN]));
        $this->assertTrue(Gate::forUser($admin)->denies('update-role', [$user, User::ROLE_ADMIN]));

        $this->assertTrue(Gate::forUser($superadmin)->allows('update-role', [$user, User::ROLE_ADMIN]));
        $this->assertTrue(Gate::forUser($superadmin)->denies('update-role', [$superadmin, User::ROLE_ADMIN]));
        $this->assertTrue(Gate::forUser($superadmin)->denies('update-role', [$user, User::ROLE_SUPERADMIN]));
        $this->assertTrue(Gate::forUser($superadmin)->denies('update-role', [$superadmin, User::ROLE_USER]));
    }

    public function test_account_management_policy_is_superadmin_only_and_never_self_service(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();

        $this->assertTrue(Gate::forUser($user)->denies('manageAccount', $user));
        $this->assertTrue(Gate::forUser($admin)->denies('manageAccount', $user));
        $this->assertTrue(Gate::forUser($superadmin)->denies('manageAccount', $superadmin));
        $this->assertTrue(Gate::forUser($superadmin)->allows('manageAccount', $user));
    }

    public function test_superadmin_gate_accepts_only_the_superadmin_role(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();

        $this->assertTrue(Gate::forUser($superadmin)->allows('access-superadmin'));
        $this->assertTrue(Gate::forUser($admin)->denies('access-superadmin'));
        $this->assertTrue(Gate::forUser($user)->denies('access-superadmin'));
        $this->assertTrue(Gate::denies('access-superadmin'));
    }

    public function test_admin_middleware_allows_admin_roles_and_denies_normal_users(): void
    {
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->get('/_test/admin')->assertOk()->assertSee('admin-ok');
        $this->actingAs($superadmin)->get('/_test/admin')->assertOk();
        $this->actingAs($user)->get('/_test/admin')->assertForbidden();

        auth()->logout();
        $this->get('/_test/admin')->assertRedirect(route('login', absolute: false));
    }

    public function test_superadmin_middleware_is_separate_from_admin_middleware(): void
    {
        $superadmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($superadmin)->get('/_test/superadmin')->assertOk()->assertSee('superadmin-ok');
        $this->actingAs($admin)->get('/_test/superadmin')->assertForbidden();
        $this->actingAs($user)->get('/_test/superadmin')->assertForbidden();

        auth()->logout();
        $this->get('/_test/superadmin')->assertRedirect(route('login', absolute: false));
    }

    public function test_suspended_and_banned_privileged_accounts_cannot_bypass_middleware(): void
    {
        foreach ([User::STATUS_SUSPENDED, User::STATUS_BANNED] as $status) {
            $admin = User::factory()->admin()->create(['status' => $status]);
            $superadmin = User::factory()->superAdmin()->create(['status' => $status]);

            $this->actingAs($admin)->get('/_test/admin')
                ->assertRedirect(route('login', absolute: false));
            $this->assertGuest();

            $this->actingAs($superadmin)->get('/_test/superadmin')
                ->assertRedirect(route('login', absolute: false));
            $this->assertGuest();
        }
    }
}
