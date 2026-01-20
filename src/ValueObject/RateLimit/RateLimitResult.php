<?php

namespace App\ValueObject\RateLimit;

final class RateLimitResult
{
    public function __construct(
        public readonly bool  $allowed,
        public readonly ?bool $limited = null,
        public readonly int   $limit = 0,
        public readonly int   $remaining = 0,
        public readonly int   $used = 0,
        public readonly int   $reset = 0,
        public readonly int   $retryAfter = 0,
        public readonly int   $resetTime = 0
    )
    {
    }

    /**
     * Creates a RateLimitResult instance for an allowed request.
     */
    public static function allowed(int $limit, int $remaining, int $used, int $reset): self
    {
        return new self(
            allowed: true,
            limited: false,
            limit: $limit,
            remaining: $remaining,
            used: $used,
            reset: $reset,
            retryAfter: 0,
            resetTime: 0
        );
    }

    /**
     * Creates a RateLimitResult instance for an exceeded request.
     */
    public static function exceeded(int $limit, int $remaining, int $used, int $reset, int $retryAfter, int $resetTime): self
    {
        return new self(
            allowed: false,
            limited: true,
            limit: $limit,
            remaining: $remaining,
            used: $used,
            reset: $reset,
            retryAfter: $retryAfter,
            resetTime: $resetTime
        );
    }

    /**
     * Checks if the request is rate-limited.
     *
     * @return bool
     */
    public function isLimited(): bool
    {
        return $this->limited ?? !$this->allowed;
    }
}
