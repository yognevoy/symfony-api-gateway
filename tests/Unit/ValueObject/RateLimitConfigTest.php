<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\RateLimitConfig;
use PHPUnit\Framework\TestCase;

class RateLimitConfigTest extends TestCase
{
    public function testFromArrayCreatesInstance(): void
    {
        $config = RateLimitConfig::fromArray([
            'enabled' => true,
            'limit' => 100,
            'period' => 60,
            'per_client' => true
        ]);

        $this->assertTrue($config->isEnabled());
        $this->assertEquals(100, $config->limit);
        $this->assertEquals(60, $config->period);
        $this->assertTrue($config->perClient);
    }

    public function testDisabledReturnsDisabledConfig(): void
    {
        $config = RateLimitConfig::disabled();

        $this->assertFalse($config->isEnabled());
        $this->assertEquals(0, $config->limit);
        $this->assertEquals(0, $config->period);
        $this->assertFalse($config->perClient);
    }
}
