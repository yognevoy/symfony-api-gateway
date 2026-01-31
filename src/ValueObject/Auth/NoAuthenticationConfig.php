<?php

namespace App\ValueObject\Auth;

/**
 * Configuration for no authentication (public access).
 */
final class NoAuthenticationConfig implements AuthenticationConfigInterface
{
    public function __construct()
    {
    }

    public static function fromArray(array $config): self
    {
        return new self();
    }

    public function getType(): string
    {
        return 'none';
    }
}
