<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Route not found', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
