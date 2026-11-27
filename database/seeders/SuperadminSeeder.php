<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\EthiopianPhone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperadminSeeder extends Seeder
{
    /**
     * Create the initial superadmin from environment-backed configuration.
     *
     * This is the only supported way to obtain the superadmin role. Existing
     * accounts are never promoted, overwritten, or given a new password.
     */
    public function run(): void
    {
        $name = config('services.superadmin.name');
        $phone = config('services.superadmin.phone');
        $password = config('services.superadmin.password');

        if ($this->isMissing($name) && $this->isMissing($phone) && $this->isMissing($password)) {
            $this->note('Superadmin seeding skipped: set SUPERADMIN_NAME, SUPERADMIN_PHONE and SUPERADMIN_PASSWORD to create one.');

            return;
        }

        if ($this->isMissing($name) || $this->isMissing($phone) || $this->isMissing($password)) {
            throw new RuntimeException(
                'SUPERADMIN_NAME, SUPERADMIN_PHONE and SUPERADMIN_PASSWORD must all be set in the environment.'
            );
        }

        $normalizedPhone = EthiopianPhone::normalize((string) $phone);

        if ($normalizedPhone === null) {
            throw new RuntimeException('SUPERADMIN_PHONE must be a valid Ethiopian phone number.');
        }

        if (User::where('role', User::ROLE_SUPERADMIN)->exists()) {
            $this->note('A superadmin already exists; no new privileged account was created.');

            return;
        }

        if (User::where('phone', $normalizedPhone)->exists()) {
            $this->note('SUPERADMIN_PHONE belongs to an existing account; that account was not promoted or modified.');

            return;
        }

        $user = new User;

        // forceFill is intentional: "role" is not mass assignable so that
        // public requests can never set it.
        $user->forceFill([
            'name' => $name,
            'phone' => $normalizedPhone,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPERADMIN,
            'phone_verified_at' => now(),
        ])->save();
    }

    private function isMissing(mixed $value): bool
    {
        return ! is_string($value) || trim($value) === '';
    }

    private function note(string $message): void
    {
        $this->command?->warn($message);
    }
}
