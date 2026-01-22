<?php

namespace App\ValueObject;

final class CacheConfig
{
    public function __construct(
        public readonly ?int $ttl = null
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['ttl'] ?? null
        );
    }

    public function isEnabled(): bool
    {
        return $this->ttl !== null && $this->ttl > 0;
    }
}
