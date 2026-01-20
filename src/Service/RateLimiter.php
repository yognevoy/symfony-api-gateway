<?php

namespace App\Service;

use App\ValueObject\RateLimitResult;
use App\ValueObject\RouteConfig;
use Psr\Cache\CacheItemPoolInterface;

class RateLimiter
{
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
                $data = [
                    'count' => 1,
                    'window_start' => $windowStart
                ];
                $item->set($data);
                $item->expiresAfter($routeConfig->rateLimit->period * 2);
                $this->cache->save($item);

                return new RateLimitResult(
                    allowed: true,
                    limited: false,
                    limit: $routeConfig->rateLimit->limit,
                    remaining: $routeConfig->rateLimit->limit - 1,
                    used: 1,
                    reset: $windowEnd,
                    retryAfter: 0,
                    resetTime: 0
                );
            }

            $data = $item->get();
            $currentWindowStart = $data['window_start'];

            if ($currentWindowStart !== $windowStart) {
                $data = [
                    'count' => 1,
                    'window_start' => $windowStart
                ];
                $item->set($data);
                $item->expiresAfter($routeConfig->rateLimit->period * 2);
                $this->cache->save($item);

                return new RateLimitResult(
                    allowed: true,
                    limited: false,
                    limit: $routeConfig->rateLimit->limit,
                    remaining: $routeConfig->rateLimit->limit - 1,
                    used: 1,
                    reset: $windowEnd,
                    retryAfter: 0,
                    resetTime: 0
                );
            }

            $data['count']++;
            $item->set($data);
            $item->expiresAfter($routeConfig->rateLimit->period * 2);
            $this->cache->save($item);

            $currentCount = $data['count'];
            $allowed = $currentCount <= $routeConfig->rateLimit->limit;

            if (!$allowed) {
                $retryAfter = max(1, $windowEnd - $timestamp);

                return new RateLimitResult(
                    allowed: false,
                    limited: true,
                    limit: $routeConfig->rateLimit->limit,
                    remaining: 0,
                    used: $currentCount,
                    reset: $windowEnd,
                    retryAfter: $retryAfter,
                    resetTime: $windowEnd
                );
            }

            $remaining = max(0, $routeConfig->rateLimit->limit - $currentCount);

            return new RateLimitResult(
                allowed: true,
                limited: false,
                limit: $routeConfig->rateLimit->limit,
                remaining: $remaining,
                used: $currentCount,
                reset: $windowEnd,
                retryAfter: 0,
                resetTime: 0
            );
        } catch (\Exception $e) {
            return new RateLimitResult(
                allowed: true,
                limited: false,
                limit: $routeConfig->rateLimit->limit,
                remaining: $routeConfig->rateLimit->limit,
                used: 0,
                reset: time() + $routeConfig->rateLimit->period,
                retryAfter: 0,
                resetTime: 0
            );
        }
    }

    /**
     * Generate a cache key for rate limiting.
     */
    private function generateCacheKey(string $routeName, string $identifier): string
    {
        return "rate_limit.{$routeName}.{$identifier}";
    }
}
