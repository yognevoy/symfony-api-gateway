<?php

namespace App\Tests\Unit\Service;

use App\Service\Auth\AuthenticatorInterface;
use App\Service\AuthenticationManager;
use App\ValueObject\Auth\ApiKeyAuthenticationConfig;
use App\ValueObject\Auth\BasicAuthenticationConfig;
use App\ValueObject\Auth\JwtAuthenticationConfig;
use App\ValueObject\Auth\NoAuthenticationConfig;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AuthenticationManagerTest extends TestCase
{
    private AuthenticationManager $authenticationManager;
    private AuthenticatorInterface $mockApiKeyAuthenticator;
    private AuthenticatorInterface $mockBasicAuthenticator;
    private AuthenticatorInterface $mockJwtAuthenticator;

    protected function setUp(): void
    {
        $this->mockApiKeyAuthenticator = $this->createMock(AuthenticatorInterface::class);
        $this->mockBasicAuthenticator = $this->createMock(AuthenticatorInterface::class);
        $this->mockJwtAuthenticator = $this->createMock(AuthenticatorInterface::class);

        $authenticators = [
            $this->mockApiKeyAuthenticator,
            $this->mockBasicAuthenticator,
            $this->mockJwtAuthenticator,
        ];

        $this->authenticationManager = new AuthenticationManager($authenticators);
    }

    public function testValidateReturnsTrueForNoAuthenticationConfig(): void
    {
        $request = new Request();
        $config = new NoAuthenticationConfig();

        $result = $this->authenticationManager->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateDelegatesToCorrectAuthenticator(): void
    {
        $request = new Request();
        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key']
        );

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(true);

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('validate')
            ->with($request, $config)
            ->willReturn(true);

        $this->mockBasicAuthenticator
            ->expects($this->never())
            ->method('supports');

        $this->mockJwtAuthenticator
            ->expects($this->never())
            ->method('supports');

        $result = $this->authenticationManager->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateReturnsAuthenticatorResult(): void
    {
        $request = new Request();
        $config = new BasicAuthenticationConfig(
            users: [['username' => 'user', 'password' => 'pass']]
        );

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(false);

        $this->mockBasicAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(true);

        $this->mockBasicAuthenticator
            ->expects($this->once())
            ->method('validate')
            ->with($request, $config)
            ->willReturn(true);

        $result = $this->authenticationManager->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForUnsupportedAuthenticationType(): void
    {
        $request = new Request();
        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            secret: 'secret'
        );

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(false);

        $this->mockBasicAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(false);

        $this->mockJwtAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(false);

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage("Unsupported authentication type: jwt");

        $this->authenticationManager->validate($request, $config);
    }

    public function testValidateHandlesAuthenticatorExceptions(): void
    {
        $request = new Request();
        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key']
        );

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('supports')
            ->with($config)
            ->willReturn(true);

        $this->mockApiKeyAuthenticator
            ->expects($this->once())
            ->method('validate')
            ->with($request, $config)
            ->willThrowException(new \Exception('Auth failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Auth failed');

        $this->authenticationManager->validate($request, $config);
    }

    public function testValidateWithEmptyAuthenticators(): void
    {
        $emptyAuthManager = new AuthenticationManager([]);

        $request = new Request();
        $config = new ApiKeyAuthenticationConfig(
            header: 'X-API-Key',
            keys: ['valid-key']
        );

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage("Unsupported authentication type: api_key");

        $emptyAuthManager->validate($request, $config);
    }
}
