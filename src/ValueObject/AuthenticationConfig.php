<?php

namespace App\ValueObject;

final class AuthenticationConfig
{
    private function __construct(
        public readonly string  $type,
        public readonly ?string $header = null,
        public readonly ?string $prefix = null,
        public readonly array   $keys = [],
        public readonly array   $users = []
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['type'],
            $config['header'] ?? null,
            $config['prefix'] ?? null,
            $config['keys'] ?? [],
            $config['users'] ?? []
        );
    }
}
