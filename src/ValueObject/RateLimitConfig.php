<?php

namespace App\ValueObject;

final class RateLimitConfig
{
    private function __construct(
        public readonly int $limit,
        public readonly int $period
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
            $config['period']
        );
    }

    /**
     * Creates a disabled rate limit configuration.
     *
     * @return array
     */
    public static function disabled(): array
    {
        return [
            'limit' => 0,
            'period' => 0
        ];
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
