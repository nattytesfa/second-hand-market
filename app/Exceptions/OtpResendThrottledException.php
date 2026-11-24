<?php

namespace App\Exceptions;

use RuntimeException;

class OtpResendThrottledException extends RuntimeException
{
    public function __construct(public readonly int $availableIn)
    {
        parent::__construct("An OTP was already sent. Try again in {$availableIn} seconds.");
    }
}
