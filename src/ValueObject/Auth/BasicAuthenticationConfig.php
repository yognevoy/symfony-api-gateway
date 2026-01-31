<?php

namespace App\ValueObject\Auth;

/**
 * Configuration for basic authentication.
 */
final class BasicAuthenticationConfig implements AuthenticationConfigInterface
{
    public function __construct(
        public readonly array $users = []
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['users'] ?? []
        );
    }

    public function getType(): string
    {
        return 'basic';
    }
}
