<?php

namespace App\ValueObject;

/**
 * Configuration for no authentication (public access).
 */
final class NoAuthenticationConfig implements AuthenticationConfigInterface
{
    private function __construct()
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
