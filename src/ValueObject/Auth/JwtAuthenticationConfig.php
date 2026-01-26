<?php

namespace App\ValueObject\Auth;

/**
 * Configuration for JWT authentication.
 */
final class JwtAuthenticationConfig implements AuthenticationConfigInterface
{
    private function __construct(
        public readonly ?string $header = 'Authorization',
        public readonly string $prefix = 'Bearer ',
        public readonly string $secret = ''
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['header'] ?? 'Authorization',
            $config['prefix'] ?? 'Bearer ',
            $config['secret'] ?? $_ENV['JWT_SECRET'] ?? ''
        );
    }

    public function getType(): string
    {
        return 'jwt';
    }
}
