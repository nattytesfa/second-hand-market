<?php

namespace App\Services;

use App\Exceptions\OtpResendThrottledException;
use App\Models\PhoneVerification;
use App\Services\Contracts\OtpProvider;
use App\Support\EthiopianPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class PhoneVerificationService
{
    public const PURPOSE_VERIFY = 'verify';

    public const PURPOSE_RESET = 'reset';

    public function __construct(private readonly OtpProvider $otp) {}

    public function send(string $phone): void
    {
        $this->issue($phone, self::PURPOSE_VERIFY);
    }

    public function verify(string $phone, string $code): bool
    {
        return $this->check($phone, $code, self::PURPOSE_VERIFY);
    }

    public function forget(string $phone): void
    {
        $this->invalidate($phone, self::PURPOSE_VERIFY);
    }

    public function sendPasswordResetCode(string $phone): void
    {
        $this->issue($phone, self::PURPOSE_RESET);
    }

    public function verifyPasswordResetCode(string $phone, string $code): bool
    {
        return $this->check($phone, $code, self::PURPOSE_RESET);
    }

    public function forgetPasswordResetCode(string $phone): void
    {
        $this->invalidate($phone, self::PURPOSE_RESET);
    }

    /**
     * Issue an OTP challenge via the configured provider. A DB record is kept
     * for throttle, purpose separation, attempt limiting, and single-use state.
     *
     * @throws OtpResendThrottledException
     */
    public function issue(string $phone, string $purpose): void
    {
        $phone = EthiopianPhone::normalize($phone);

        if ($phone === null) {
            throw new InvalidArgumentException('A valid Ethiopian phone number is required to issue an OTP.');
        }

        $this->assertNotThrottled($phone, $purpose);

        DB::transaction(function () use ($phone, $purpose): void {
            PhoneVerification::where('phone', $phone)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            PhoneVerification::create([
                'phone' => $phone,
                'purpose' => $purpose,
                // AfroMessage owns the code; store an unusable placeholder hash.
                'code_hash' => Hash::make(bin2hex(random_bytes(32))),
                'expires_at' => now()->addMinutes((int) config('otp.expiry_minutes')),
                'attempts' => 0,
            ]);
        });

        try {
            $this->otp->challenge($phone, ['purpose' => $purpose]);
        } catch (\Throwable $exception) {
            // No challenge was delivered, so do not leave an OTP row behind:
            // a retry must be possible immediately after credentials are fixed.
            PhoneVerification::where('phone', $phone)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->delete();

            throw $exception;
        }
    }

    public function check(string $phone, string $code, string $purpose = self::PURPOSE_VERIFY): bool
    {
        $phone = EthiopianPhone::normalize($phone);

        if ($phone === null || $this->invalidCodeFormat($code)) {
            return false;
        }

        return DB::transaction(function () use ($phone, $code, $purpose): bool {
            /** @var PhoneVerification|null $record */
            $record = PhoneVerification::where('phone', $phone)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return false;
            }

            if ($record->isExpired() || $record->attempts >= (int) config('otp.max_attempts')) {
                $record->forceFill(['consumed_at' => now()])->save();

                return false;
            }

            $record->increment('attempts');
            $record->refresh();

            $valid = $this->otp->verify($phone, $code, ['purpose' => $purpose]);

            if (! $valid) {
                if ($record->attempts >= (int) config('otp.max_attempts')) {
                    $record->forceFill(['consumed_at' => now()])->save();
                }

                return false;
            }

            $record->forceFill(['consumed_at' => now()])->save();

            return true;
        });
    }

    private function assertNotThrottled(string $phone, string $purpose): void
    {
        /** @var PhoneVerification|null $latest */
        $latest = PhoneVerification::where('phone', $phone)
            ->where('purpose', $purpose)
            ->latest('created_at')
            ->first();

        if (! $latest) {
            return;
        }

        $resendSeconds = max(1, (int) config('otp.resend_seconds'));
        $elapsed = $latest->created_at->diffInSeconds(now());

        if ($elapsed < $resendSeconds) {
            throw new OtpResendThrottledException(max(1, $resendSeconds - $elapsed));
        }
    }

    private function invalidate(string $phone, string $purpose): void
    {
        $phone = EthiopianPhone::normalize($phone);

        if ($phone === null) {
            return;
        }

        PhoneVerification::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }

    private function invalidCodeFormat(string $code): bool
    {
        $length = max(1, (int) config('otp.length', 6));

        return preg_match('/^\d{'.$length.'}$/', $code) !== 1;
    }
}
