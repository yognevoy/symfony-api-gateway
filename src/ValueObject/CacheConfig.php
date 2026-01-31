<?php

namespace App\ValueObject;

final class CacheConfig
{
    public function __construct(
        public readonly ?int $ttl = null
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
            $config['ttl'] ?? null
        );
    }

    /**
     * Creates a disabled cache configuration.
     *
     * @return CacheConfig
     */
    public static function disabled(): self
    {
        return new self(
            ttl: null
        );
    }

    /**
     * Checks if cache is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->ttl !== null && $this->ttl > 0;
    }
}
