<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\TimeoutConfig;
use PHPUnit\Framework\TestCase;

class TimeoutConfigTest extends TestCase
{
    public function testFromArrayCreatesInstance(): void
    {
        $config = TimeoutConfig::fromArray([
            'duration' => 30,
            'retries' => 3,
            'retryDelay' => 1000
        ]);

        $this->assertEquals(30, $config->duration);
        $this->assertEquals(3, $config->retries);
        $this->assertEquals(1000, $config->retryDelay);
    }

    public function testDisabledReturnsDisabledConfig(): void
    {
        $config = TimeoutConfig::disabled();

        $this->assertEquals(TimeoutConfig::DEFAULT_DURATION, $config->duration);
        $this->assertEquals(TimeoutConfig::DEFAULT_RETRIES, $config->retries);
        $this->assertEquals(TimeoutConfig::DEFAULT_RETRY_DELAY, $config->retryDelay);
    }
}
