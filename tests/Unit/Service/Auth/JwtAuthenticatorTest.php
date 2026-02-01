<?php

namespace App\Tests\Unit\Service\Auth;

use App\Exception\Auth\JwtAuthenticationException;
use App\Service\Auth\JwtAuthenticator;
use App\ValueObject\Auth\JwtAuthenticationConfig;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class JwtAuthenticatorTest extends TestCase
{
    private const JWT_SECRET = 'w[xN>ctm2&j8h<.<)l*X@@pc)]wL_t4X6<}kMA:bj:l';
    private JwtAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new JwtAuthenticator();
    }

    public function testValidateReturnsTrueForValidJwtToken(): void
    {
        $secret = self::JWT_SECRET;
        $payload = [
            'iss' => 'test-app',
            'aud' => 'test-audience',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'userId' => 123,
                'role' => 'admin'
            ]
        ];

        $jwt = JWT::encode($payload, $secret, 'HS256');

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: $secret
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForInvalidToken(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer invalid-token');

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: self::JWT_SECRET
        );

        $this->expectException(JwtAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateThrowsExceptionForTokenWithInvalidSignature(): void
    {
        $secret1 = self::JWT_SECRET;
        $secret2 = 'u;W%xu%b[#o<mCGFg<:y|J.4iloqK@]2?Ji=YOl[C){';

        $payload = [
            'iss' => 'test-app',
            'aud' => 'test-audience',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $jwt = JWT::encode($payload, $secret1, 'HS256');

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: $secret2
        );

        $this->expectException(JwtAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateThrowsExceptionForExpiredToken(): void
    {
        $secret = self::JWT_SECRET;
        $payload = [
            'iss' => 'test-app',
            'aud' => 'test-audience',
            'iat' => time() - 3600,
            'exp' => time() - 100, // Expired
        ];

        $jwt = JWT::encode($payload, $secret, 'HS256');

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: $secret
        );

        $this->expectException(JwtAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testValidateUsesDefaultHeaderWhenNotSpecified(): void
    {
        $secret = self::JWT_SECRET;
        $payload = [
            'iss' => 'test-app',
            'aud' => 'test-audience',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $jwt = JWT::encode($payload, $secret, 'HS256');

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            secret: $secret
        );

        $result = $this->authenticator->validate($request, $config);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionForMissingToken(): void
    {
        $request = new Request();

        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: self::JWT_SECRET
        );

        $this->expectException(JwtAuthenticationException::class);
        $this->authenticator->validate($request, $config);
    }

    public function testSupportsReturnsTrueForJwtAuthConfig(): void
    {
        $config = new JwtAuthenticationConfig(
            header: 'Authorization',
            prefix: 'Bearer ',
            secret: 'test-secret'
        );

        $result = $this->authenticator->supports($config);

        $this->assertTrue($result);
    }
}
