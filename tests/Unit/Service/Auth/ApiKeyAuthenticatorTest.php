<?php

namespace App\Tests\Unit\Service\Auth;

use App\Exception\Auth\ApiKeyAuthenticationException;
use App\Service\Auth\ApiKeyAuthenticator;
use App\ValueObject\Auth\ApiKeyAuthenticationConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiKeyAuthenticatorTest extends TestCase
{
    private ApiKeyAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new ApiKeyAuthenticator();
    }

    public function testValidateReturnsTrueForValidApiKey(): void
    {
        $request = new Request();
        $request->headers->set('X-API-Key', 'valid-key-123');

        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key-123', 'another-valid-key']
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForInvalidApiKey(): void
    {
        $request = new Request();
        $request->headers->set('X-API-Key', 'invalid-key');

        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key-123', 'another-valid-key']
        );

        $this->expectException(ApiKeyAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateUsesDefaultHeaderWhenNotSpecified(): void
    {
        $request = new Request();
        $request->headers->set('X-API-Key', 'valid-key-123');

        $config = new ApiKeyAuthenticationConfig(
            keys: ['valid-key-123', 'another-valid-key']
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionWhenApiKeyNotProvided(): void
    {
        $request = new Request();

        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key-123', 'another-valid-key']
        );

        $this->expectException(ApiKeyAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateHandlesApiKeyWithPrefix(): void
    {
        $request = new Request();
        $request->headers->set('X-API-Key', 'Bearer valid-key-123');

        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            prefix: 'Bearer ',
            keys: ['valid-key-123', 'another-valid-key']
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testSupportsReturnsTrueForApiKeyConfig(): void
    {
        $config = new ApiKeyAuthenticationConfig(
            keys: ['key1', 'key2']
        );

        $result = $this->authenticator->supports($config);

        $this->assertTrue($result);
    }
}
