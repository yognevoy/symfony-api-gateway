<?php

namespace App\ValueObject;

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
     * Checks if the request is rate-limited.
     *
     * @return bool
     */
    public function isLimited(): bool
    {
        return $this->limited ?? !$this->allowed;
    }
}
