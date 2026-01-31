<?php

namespace App\Tests\Unit\ValueObject\Auth;

use App\ValueObject\Auth\ApiKeyAuthenticationConfig;
use App\ValueObject\Auth\BasicAuthenticationConfig;
use App\ValueObject\Auth\JwtAuthenticationConfig;
use App\ValueObject\Auth\NoAuthenticationConfig;
use App\ValueObject\AuthenticationConfig;
use PHPUnit\Framework\TestCase;

class AuthenticationConfigTest extends TestCase
{
    public function testFromArrayCreatesNoAuthConfig(): void
    {
        $config = AuthenticationConfig::fromArray(['type' => 'none']);

        $this->assertInstanceOf(NoAuthenticationConfig::class, $config);
    }

    public function testFromArrayCreatesApiKeyConfig(): void
    {
        $config = AuthenticationConfig::fromArray([
            'type' => 'api_key',
            'keys' => ['key1', 'key2'],
            'header' => 'X-API-Key'
        ]);

        $this->assertInstanceOf(ApiKeyAuthenticationConfig::class, $config);
        $this->assertEquals(['key1', 'key2'], $config->keys);
        $this->assertEquals('X-API-Key', $config->header);
    }

    public function testFromArrayCreatesBasicAuthConfig(): void
    {
        $config = AuthenticationConfig::fromArray([
            'type' => 'basic',
            'users' => [
                ['username' => 'user1', 'password' => 'pass1']
            ]
        ]);

        $this->assertInstanceOf(BasicAuthenticationConfig::class, $config);
        $this->assertEquals([
            ['username' => 'user1', 'password' => 'pass1']
        ], $config->users);
    }

    public function testFromArrayCreatesJwtConfig(): void
    {
        $config = AuthenticationConfig::fromArray([
            'type' => 'jwt',
            'secret' => 'test-secret',
            'header' => 'Authorization'
        ]);

        $this->assertInstanceOf(JwtAuthenticationConfig::class, $config);
        $this->assertEquals('test-secret', $config->secret);
        $this->assertEquals('Authorization', $config->header);
    }

    public function testDisabledReturnsNoAuthenticationConfig(): void
    {
        $config = AuthenticationConfig::disabled();

        $this->assertInstanceOf(NoAuthenticationConfig::class, $config);
        $this->assertEquals('none', $config->getType());
    }
}
