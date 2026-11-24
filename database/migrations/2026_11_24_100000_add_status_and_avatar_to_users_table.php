<?php

use App\Models\User;
use App\Support\EthiopianPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add profile/account-status fields and normalize any existing phone
     * numbers so the unique phone index operates on canonical values.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default(User::STATUS_ACTIVE)->index()->after('password');
            }
        });

        DB::table('users')
            ->select('id', 'phone')
            ->whereNotNull('phone')
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    $normalized = EthiopianPhone::normalize($user->phone);

                    if ($normalized === null || $normalized === $user->phone) {
                        continue;
                    }

                    $taken = DB::table('users')
                        ->where('phone', $normalized)
                        ->where('id', '!=', $user->id)
                        ->exists();

                    if ($taken) {
                        Log::warning('Skipped normalization of a legacy phone number because its canonical form is already in use.', [
                            'user_id' => $user->id,
                        ]);

                        continue;
                    }

                    DB::table('users')->where('id', $user->id)->update(['phone' => $normalized]);
                }
            });

        $hasUniquePhoneIndex = collect(Schema::getIndexes('users'))->contains(
            fn (array $index): bool => ! empty($index['unique']) && ($index['columns'] ?? []) === ['phone']
        );

        if (! $hasUniquePhoneIndex) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('phone', 'users_phone_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });
    }
};
