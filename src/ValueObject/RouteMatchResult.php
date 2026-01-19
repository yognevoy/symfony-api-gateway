<?php

namespace App\ValueObject;

final class RouteMatchResult
{
    public function __construct(
        public readonly RouteConfig $route,
        public readonly array       $variables
    )
    {
    }

    public static function create(RouteConfig $route, array $variables): self
    {
        return new self($route, $variables);
    }
}
