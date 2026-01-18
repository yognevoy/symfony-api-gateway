<?php

namespace App\Exception\Auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BasicAuthenticationException extends UnauthorizedHttpException
{
    public function __construct(string $message = 'Basic authentication failed', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct('Basic', $message, $previous, $code);
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid username or password');
    }

    public static function missingCredentials(): self
    {
        return new self('Missing username or password');
    }
}
