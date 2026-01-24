<?php

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
    }

    public function process(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        $this->logger->info('Request received', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
        ]);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        $this->logger->info('Request processed', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'status_code' => $response->getStatusCode(),
            'duration' => round($duration, 2),
        ]);

        return $response;
    }
}
