<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\ResponseFilterConfig;
use PHPUnit\Framework\TestCase;

class ResponseFilterConfigTest extends TestCase
{
    public function testFromArrayCreatesInstance(): void
    {
        $config = ResponseFilterConfig::fromArray([
            'include' => ['field1', 'field2'],
            'exclude' => ['field3']
        ]);

        $this->assertEquals(['field1', 'field2'], $config->include);
        $this->assertEquals(['field3'], $config->exclude);
    }

    public function testFromArrayWithEmptyConfig(): void
    {
        $config = ResponseFilterConfig::fromArray([]);

        $this->assertEquals([], $config->include);
        $this->assertEquals([], $config->exclude);
    }

    public function testIsEmptyReturnsTrueWhenIncludeAndExcludeAreEmpty(): void
    {
        $config = new ResponseFilterConfig([], []);

        $this->assertTrue($config->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenIncludeHasValues(): void
    {
        $config = new ResponseFilterConfig(['field1'], []);

        $this->assertFalse($config->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenExcludeHasValues(): void
    {
        $config = new ResponseFilterConfig([], ['field1']);

        $this->assertFalse($config->isEmpty());
    }
}
