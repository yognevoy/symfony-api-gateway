<?php

namespace App\Service\Auth;

use App\Exception\Auth\JwtAuthenticationException;
use App\ValueObject\Auth\AuthenticationConfigInterface;
use App\ValueObject\Auth\JwtAuthenticationConfig;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\HttpFoundation\Request;

class JwtAuthenticator implements AuthenticatorInterface
{
    private const DEFAULT_ALGORITHM = 'HS256';

    public function validate(Request $request, AuthenticationConfigInterface $config): bool
    {
        if (!$config instanceof JwtAuthenticationConfig) {
            throw new \LogicException('Invalid configuration type passed to JwtAuthenticator');
        }

        $headerName = $config->header;
        $token = $request->headers->get($headerName);

        if ($config->prefix !== null && $token !== null) {
            $token = $this->normalizeToken($token, $config->prefix);
        }

        if ($token === null) {
            throw JwtAuthenticationException::missingToken();
        }

        try {
            JWT::decode($token, new Key($config->secret, self::DEFAULT_ALGORITHM));
            return true;
        } catch (SignatureInvalidException $e) {
            throw JwtAuthenticationException::invalidToken('Invalid token signature');
        } catch (BeforeValidException $e) {
            throw JwtAuthenticationException::invalidToken('Token not yet valid');
        } catch (ExpiredException $e) {
            throw JwtAuthenticationException::invalidToken('Token expired');
        } catch (\Exception $e) {
            throw JwtAuthenticationException::invalidToken($e->getMessage());
        }
    }

    protected function normalizeToken(string $token, string $prefix): ?string
    {
        if (str_starts_with($token, $prefix)) {
            return trim(substr($token, strlen($prefix)));
        } else {
            throw JwtAuthenticationException::invalidFormat();
        }
    }

    public function supports(AuthenticationConfigInterface $config): bool
    {
        return $config instanceof JwtAuthenticationConfig;
    }
}
