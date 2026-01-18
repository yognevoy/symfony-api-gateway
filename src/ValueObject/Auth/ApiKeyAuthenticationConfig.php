<?php

namespace App\ValueObject\Auth;

/**
 * Configuration for API key authentication.
 */
final class ApiKeyAuthenticationConfig implements AuthenticationConfigInterface
{
    private function __construct(
        public readonly ?string $header = null,
        public readonly ?string $prefix = null,
        public readonly array $keys = []
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['header'] ?? null,
            $config['prefix'] ?? null,
            $config['keys'] ?? []
        );
    }

    public function getType(): string
    {
        return 'api_key';
    }
}
