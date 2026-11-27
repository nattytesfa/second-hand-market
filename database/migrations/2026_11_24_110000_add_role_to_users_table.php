<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a single role column. Existing accounts remain ordinary users;
     * superadmins are created only by the dedicated seeder.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('role', 20)
                    ->default(User::ROLE_USER)
                    ->index()
                    ->after('status');
            });
        }

        DB::table('users')
            ->whereNull('role')
            ->update(['role' => User::ROLE_USER]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('role');
            });
        }
    }
};
