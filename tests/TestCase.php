<?php

namespace Tests;

use App\Services\Contracts\OtpProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\FakeOtpProvider;

abstract class TestCase extends BaseTestCase
{
    protected FakeOtpProvider $sms;

    protected function setUp(): void
    {
        parent::setUp();

        // Never send real SMS messages during automated tests.
        $this->sms = new FakeOtpProvider();
        $this->app->instance(OtpProvider::class, $this->sms);
    }
}
