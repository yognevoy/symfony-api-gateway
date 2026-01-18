<?php

namespace App\ValueObject;

final class RouteConfig
{
    private function __construct(
        public readonly string               $path,
        public readonly string               $target,
        public readonly array                $methods,
        public readonly AuthenticationConfig $authentication,
        public readonly int                  $rateLimit
    )
    {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            $config['path'],
            $config['target'],
            $config['methods'],
            AuthenticationConfig::fromArray($config['authentication']),
            $config['rate_limit']
        );
    }
}
