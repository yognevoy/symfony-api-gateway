<?php

namespace App\ValueObject;

use App\ValueObject\Auth\AuthenticationConfigInterface;

final class RouteConfig
{
    private function __construct(
        public readonly string                        $name,
        public readonly string                        $path,
        public readonly string                        $target,
        public readonly array                         $methods,
        public readonly AuthenticationConfigInterface $authentication,
        public readonly RateLimitConfig               $rateLimit,
        public readonly ResponseFilterConfig          $responseFilter,
        public readonly TimeoutConfig                 $timeout,
        public readonly CacheConfig                   $cache,
        public readonly array                         $middleware,
        public readonly LoggingConfig                 $logging
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['name'],
            $config['path'],
            $config['target'],
            $config['methods'],
            AuthenticationConfig::fromArray($config['authentication']),
            RateLimitConfig::fromArray($config['rate_limit'] ?? RateLimitConfig::disabled()),
            ResponseFilterConfig::fromArray($config['response_filter'] ?? []),
            TimeoutConfig::fromArray($config['timeout'] ?? TimeoutConfig::disabled()),
            CacheConfig::fromArray($config['cache'] ?? []),
            $config['middleware'] ?? [],
            LoggingConfig::fromArray($config['logging'] ?? LoggingConfig::disabled())
        );
    }
}
