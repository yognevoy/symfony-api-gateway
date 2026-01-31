<?php

namespace App\Service;

use App\ValueObject\RateLimit\RateLimitResult;
use App\ValueObject\RateLimit\RateLimitWindow;
use App\ValueObject\RouteConfig;
use Psr\Cache\CacheItemPoolInterface;

class RateLimiter
{
    private const TTL_MULTIPLIER = 2;

    public function __construct(
        private readonly CacheItemPoolInterface $cache
    )
    {
    }

    /**
     * Check the rate limit for a route configuration.
     */
    public function checkRateLimit(RouteConfig $routeConfig, string $identifier): RateLimitResult
    {
        $key = $this->generateCacheKey($routeConfig->name, $identifier);
        $timestamp = time();
        $windowStart = floor($timestamp / $routeConfig->rateLimit->period) * $routeConfig->rateLimit->period;
        $windowEnd = $windowStart + $routeConfig->rateLimit->period;

        try {
            $item = $this->cache->getItem($key);

            if (!$item->isHit()) {
                $window = new RateLimitWindow(
                    count: 1,
                    windowStart: $windowStart
                );
                $item->set($window);
                $item->expiresAfter($routeConfig->rateLimit->period * self::TTL_MULTIPLIER);
                $this->cache->save($item);

                return RateLimitResult::allowed(
                    limit: $routeConfig->rateLimit->limit,
                    remaining: $routeConfig->rateLimit->limit - 1,
                    used: 1,
                    reset: $windowEnd
                );
            }

            /** @var RateLimitWindow $window */
            $window = $item->get();
            $currentWindowStart = $window->windowStart;

            if ($currentWindowStart != $windowStart) {
                $window = new RateLimitWindow(
                    count: 1,
                    windowStart: $windowStart
                );
                $item->set($window);
                $item->expiresAfter($routeConfig->rateLimit->period * self::TTL_MULTIPLIER);
                $this->cache->save($item);

                return RateLimitResult::allowed(
                    limit: $routeConfig->rateLimit->limit,
                    remaining: $routeConfig->rateLimit->limit - 1,
                    used: 1,
                    reset: $windowEnd
                );
            }

            $window = $window->increment();
            $item->set($window);
            $item->expiresAfter($routeConfig->rateLimit->period * self::TTL_MULTIPLIER);
            $this->cache->save($item);

            $currentCount = $window->count;
            $allowed = $currentCount <= $routeConfig->rateLimit->limit;

            if (!$allowed) {
                $retryAfter = max(1, $windowEnd - $timestamp);

                return RateLimitResult::exceeded(
                    limit: $routeConfig->rateLimit->limit,
                    remaining: 0,
                    used: $currentCount,
                    reset: $windowEnd,
                    retryAfter: $retryAfter,
                    resetTime: $windowEnd
                );
            }

            $remaining = max(0, $routeConfig->rateLimit->limit - $currentCount);

            return RateLimitResult::allowed(
                limit: $routeConfig->rateLimit->limit,
                remaining: $remaining,
                used: $currentCount,
                reset: $windowEnd
            );
        } catch (\Throwable $e) {
            return RateLimitResult::allowed(
                limit: $routeConfig->rateLimit->limit,
                remaining: $routeConfig->rateLimit->limit,
                used: 0,
                reset: time() + $routeConfig->rateLimit->period
            );
        }
    }

    /**
     * Generate a cache key for rate limiting.
     */
    protected function generateCacheKey(string $routeName, string $identifier): string
    {
        return "rate_limit.{$routeName}.{$identifier}";
    }
}
