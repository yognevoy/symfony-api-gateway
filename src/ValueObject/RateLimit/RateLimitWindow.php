<?php

namespace App\ValueObject\RateLimit;

final class RateLimitWindow
{
    public function __construct(
        public int $count,
        public int $windowStart
    )
    {
    }

    public function increment(): self
    {
        return new self($this->count + 1, $this->windowStart);
    }
}
