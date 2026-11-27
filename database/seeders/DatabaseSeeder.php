<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Public demo users are intentionally not created; only the privileged
     * seeder is wired in. Normal accounts come from public registration.
     */
    public function run(): void
    {
        $this->call([
            SuperadminSeeder::class,
        ]);
    }
}
