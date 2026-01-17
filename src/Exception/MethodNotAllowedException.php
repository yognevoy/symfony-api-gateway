<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = 'Method not allowed', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(405, $message, $previous, [], $code);
    }
}
