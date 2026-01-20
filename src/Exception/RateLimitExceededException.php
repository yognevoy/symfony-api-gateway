<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RateLimitExceededException extends HttpException
{
    public function __construct(string $message = 'Rate limit exceeded', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(429, $message, $previous, [], $code);
    }
}
