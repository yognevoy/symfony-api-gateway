<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TargetApiException extends HttpException
{
    public function __construct(string $message = 'Failed to reach target API', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(500, $message, $previous, [], $code);
    }
}
