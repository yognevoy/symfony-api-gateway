<?php

namespace App\Exception\Auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtAuthenticationException extends UnauthorizedHttpException
{
    public function __construct(string $message = 'JWT authentication failed', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct('Bearer', $message, $previous, $code);
    }

    public static function invalidFormat(): self
    {
        return new self('Invalid JWT token format');
    }

    public static function missingToken(): self
    {
        return new self('Missing JWT token');
    }

    public static function invalidToken(string $details = ''): self
    {
        return new self('Invalid JWT token' . ($details ? ': ' . $details : ''));
    }
}
