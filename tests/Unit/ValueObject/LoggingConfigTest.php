<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\LoggingConfig;
use PHPUnit\Framework\TestCase;

class LoggingConfigTest extends TestCase
{
    public function testFromArrayCreatesInstance(): void
    {
        $config = LoggingConfig::fromArray([
            'enabled' => true,
            'level' => 'debug',
            'handlers' => ['file', 'console']
        ]);

        $this->assertTrue($config->enabled);
        $this->assertEquals('debug', $config->level);
    }

    public function testDisabledReturnsDisabledConfig(): void
    {
        $config = LoggingConfig::disabled();

        $this->assertFalse($config->enabled);
    }
}
