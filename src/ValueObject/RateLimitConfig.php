<?php

namespace App\ValueObject;

final class RateLimitConfig
{
    public function __construct(
        public readonly int $limit,
        public readonly int $period,
        public readonly bool $perClient = false
    )
    {
    }

    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['limit'],
            $config['period'],
            $config['per_client'] ?? false
        );
    }

    /**
     * Creates a disabled rate limit configuration.
     *
     * @return RateLimitConfig
     */
    public static function disabled(): self
    {
        return new self(
            limit: 0,
            period: 0,
            perClient: false
        );
    }

    /**
     * Checks if rate limiting is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->limit > 0;
    }
}
