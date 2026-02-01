<?php

namespace App\Tests\Unit\Service\Auth;

use App\Exception\Auth\BasicAuthenticationException;
use App\Service\Auth\BasicAuthenticator;
use App\ValueObject\Auth\BasicAuthenticationConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BasicAuthenticatorTest extends TestCase
{
    private BasicAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new BasicAuthenticator();
    }

    public function testValidateReturnsTrueForValidCredentials(): void
    {
        $credentials = base64_encode('john:secret123');
        $request = new Request();
        $request->headers->set('Authorization', 'Basic ' . $credentials);

        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123'],
                ['username' => 'jane', 'password' => 'password456']
            ]
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForInvalidCredentials(): void
    {
        $credentials = base64_encode('john:wrongpassword');
        $request = new Request();
        $request->headers->set('Authorization', 'Basic ' . $credentials);

        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123'],
                ['username' => 'jane', 'password' => 'password456']
            ]
        );

        $this->expectException(BasicAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateThrowsExceptionForMissingAuthorizationHeader(): void
    {
        $request = new Request();

        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123']
            ]
        );

        $this->expectException(BasicAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateHandlesAlternativeAuthorizationHeader(): void
    {
        $credentials = base64_encode('jane:password456');
        $request = new Request();
        $request->headers->set('HTTP_AUTHORIZATION', 'Basic ' . $credentials);

        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123'],
                ['username' => 'jane', 'password' => 'password456']
            ]
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForInvalidAuthorizationHeader(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer invalid');

        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123']
            ]
        );

        $this->expectException(BasicAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testSupportsReturnsTrueForBasicAuthConfig(): void
    {
        $config = new BasicAuthenticationConfig(
            users: [
                ['username' => 'john', 'password' => 'secret123']
            ]
        );

        $result = $this->authenticator->supports($config);

        $this->assertTrue($result);
    }
}
