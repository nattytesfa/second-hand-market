<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SuperadminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class SuperadminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_initial_superadmin_with_a_hashed_password(): void
    {
        $this->configureSuperadmin();

        $this->seed(SuperadminSeeder::class);

        $superadmin = User::sole();

        $this->assertSame(User::ROLE_SUPERADMIN, $superadmin->role);
        $this->assertSame('0911223344', $superadmin->phone);
        $this->assertSame('Adama Administrator', $superadmin->name);
        $this->assertNotNull($superadmin->phone_verified_at);
        $this->assertNotSame('a-very-secret-password', $superadmin->password);
        $this->assertTrue(Hash::check('a-very-secret-password', $superadmin->password));
    }

    public function test_repeated_seeder_runs_do_not_create_duplicate_accounts(): void
    {
        $this->configureSuperadmin();

        $this->seed(SuperadminSeeder::class);
        $this->seed(SuperadminSeeder::class);
        $this->seed(SuperadminSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame(User::ROLE_SUPERADMIN, User::sole()->role);
    }

    public function test_an_existing_account_is_not_promoted_or_overwritten(): void
    {
        $existing = User::factory()->create([
            'name' => 'Existing Account',
            'phone' => '0911223344',
            'role' => User::ROLE_USER,
        ]);

        $this->configureSuperadmin(['password' => 'a-different-secret']);

        $this->seed(SuperadminSeeder::class);

        $fresh = $existing->fresh();

        $this->assertSame(1, User::count());
        $this->assertSame('Existing Account', $fresh->name);
        $this->assertSame(User::ROLE_USER, $fresh->role);
        $this->assertFalse(Hash::check('a-different-secret', $fresh->password));
        $this->assertTrue(Hash::check('password', $fresh->password));
    }

    public function test_a_second_superadmin_is_not_created_when_the_configured_phone_changes(): void
    {
        $this->configureSuperadmin();
        $this->seed(SuperadminSeeder::class);

        $this->configureSuperadmin(['phone' => '+251933445566']);
        $this->seed(SuperadminSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame('0911223344', User::sole()->phone);
    }

    public function test_missing_credentials_are_skipped_and_partial_credentials_fail(): void
    {
        config()->set('services.superadmin', [
            'name' => null,
            'phone' => null,
            'password' => null,
        ]);

        $this->seed(SuperadminSeeder::class);
        $this->assertSame(0, User::count());

        config()->set('services.superadmin.password', 'only-a-password');

        try {
            $this->seed(SuperadminSeeder::class);
            $this->fail('Partial superadmin credentials should be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('SUPERADMIN_NAME', $exception->getMessage());
        }

        $this->assertSame(0, User::count());
    }

    public function test_database_seeder_registers_the_superadmin_seeder(): void
    {
        $this->configureSuperadmin();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(User::ROLE_SUPERADMIN, User::sole()->role);
    }

    public function test_public_registration_cannot_create_a_superadmin_or_admin(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_SUPERADMIN] as $role) {
            $this->post('/register', [
                'name' => 'Public Registrant',
                'phone' => '0912345678',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => $role,
            ])->assertRedirect(route('otp.create', absolute: false));

            $this->assertSame(User::ROLE_USER, User::sole()->role);
            $this->assertTrue($this->isAuthenticated());

            auth()->logout();
            User::query()->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function configureSuperadmin(array $overrides = []): void
    {
        config()->set('services.superadmin', array_merge([
            'name' => 'Adama Administrator',
            'phone' => '+251911223344',
            'password' => 'a-very-secret-password',
        ], $overrides));
    }
}
