<?php

namespace App\ValueObject;

use App\ValueObject\Auth\AuthenticationConfigInterface;

final class RouteConfig
{
    public function __construct(
        public readonly string                        $name,
        public readonly string                        $path,
        public readonly string|array                  $target,
        public readonly array                         $methods,
        public readonly array                         $middleware,
        public readonly CacheConfig                   $cache,
        public readonly AuthenticationConfigInterface $authentication,
        public readonly ResponseFilterConfig          $responseFilter,
        public readonly RateLimitConfig               $rateLimit,
        public readonly TimeoutConfig                 $timeout,
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
            $config['middleware'] ?? [],
            cache: isset($config['cache'])
                ? CacheConfig::fromArray($config['cache'])
                : CacheConfig::disabled(),
            authentication: isset($config['authentication'])
                ? AuthenticationConfig::fromArray($config['authentication'])
                : AuthenticationConfig::disabled(),
            responseFilter: isset($config['response_filter'])
                ? ResponseFilterConfig::fromArray($config['response_filter'])
                : ResponseFilterConfig::disabled(),
            rateLimit: isset($config['rate_limit'])
                ? RateLimitConfig::fromArray($config['rate_limit'])
                : RateLimitConfig::disabled(),
            timeout: isset($config['timeout'])
                ? TimeoutConfig::fromArray($config['timeout'])
                : TimeoutConfig::disabled(),
            logging: isset($config['logging'])
                ? LoggingConfig::fromArray($config['logging'])
                : LoggingConfig::disabled()
        );
    }
}
