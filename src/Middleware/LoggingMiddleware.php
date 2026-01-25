<?php

namespace App\Middleware;

use App\Service\LoggingService;
use App\ValueObject\LoggingConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware implements MiddlewareInterface, ConfigurableMiddlewareInterface
{
    private ?LoggingConfig $loggingConfig = null;
    private ?string $routeName = null;

    public function __construct(
        private readonly LoggingService $loggingService
    )
    {
    }

    public function configure(mixed ...$params): void
    {
        if (isset($params[0]) && $params[0] instanceof LoggingConfig) {
            $this->loggingConfig = $params[0];
        }

        if (isset($params[1]) && is_string($params[1])) {
            $this->routeName = $params[1];
        }
    }

    public function process(Request $request, callable $next): Response
    {
        if (!$this->loggingConfig || !$this->loggingConfig->enabled) {
            return $next($request);
        }

        $startTime = microtime(true);

        $logger = $this->loggingService->getLogger($this->loggingConfig, $this->routeName);

        $logger->info('API Gateway Request Received', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
        ]);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        $logger->info('API Gateway Request Processed', [
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400 && $statusCode < 500) {
            $logger->warning('Client Error in API Gateway Request', [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status_code' => $statusCode,
                'duration_ms' => round($duration, 2),
            ]);
        }

        if ($statusCode >= 500) {
            $logger->error('Server Error in API Gateway Request', [
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'status_code' => $statusCode,
                'duration_ms' => round($duration, 2),
            ]);
        }

        return $response;
    }
}
