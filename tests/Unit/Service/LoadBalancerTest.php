<?php

namespace App\Tests\Unit\Service;

use App\Service\LoadBalancer;
use PHPUnit\Framework\TestCase;

class LoadBalancerTest extends TestCase
{
    private LoadBalancer $loadBalancer;

    protected function setUp(): void
    {
        $this->loadBalancer = new LoadBalancer();
    }

    public function testSelectTargetReturnsString(): void
    {
        $target = 'https://api.example.com';

        $result = $this->loadBalancer->selectTarget($target);

        $this->assertEquals($target, $result);
    }

    public function testSelectTargetReturnsValidTarget(): void
    {
        $targets = [
            'https://api1.example.com',
            'https://api1.example.com',
            'https://api2.example.com'
        ];

        for ($i = 0; $i < 10; $i++) {
            $result = $this->loadBalancer->selectTarget($targets);
            $this->assertContains($result, $targets);
        }
    }

    public function testSelectTargetThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Targets array cannot be empty');

        $this->loadBalancer->selectTarget([]);
    }
}
