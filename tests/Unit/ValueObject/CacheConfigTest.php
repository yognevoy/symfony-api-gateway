<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\CacheConfig;
use PHPUnit\Framework\TestCase;

class CacheConfigTest extends TestCase
{
    public function testFromArrayCreatesInstance(): void
    {
        $config = CacheConfig::fromArray(['ttl' => 300]);

        $this->assertInstanceOf(CacheConfig::class, $config);
        $this->assertEquals(300, $config->ttl);
    }

    public function testDisabledReturnsDisabledConfig(): void
    {
        $config = CacheConfig::disabled();

        $this->assertFalse($config->isEnabled());
    }

    public function testIsEnabledReturnsTrueWhenTtlIsPositive(): void
    {
        $config = new CacheConfig(300);

        $this->assertTrue($config->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenTtlIsNull(): void
    {
        $config = new CacheConfig(null);

        $this->assertFalse($config->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenTtlIsZero(): void
    {
        $config = new CacheConfig(0);

        $this->assertFalse($config->isEnabled());
    }
}
