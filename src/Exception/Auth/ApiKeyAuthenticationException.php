<?php

namespace App\Exception\Auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiKeyAuthenticationException extends UnauthorizedHttpException
{
    public function __construct(string $message = 'API key authentication failed', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct('API key', $message, $previous, $code);
    }

    public static function invalidFormat(): self
    {
        return new self('Invalid API key format');
    }

    public static function invalidApiKey(): self
    {
        return new self('Invalid API key');
    }
}
