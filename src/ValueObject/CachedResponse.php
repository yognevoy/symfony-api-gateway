<?php

namespace App\ValueObject;

class CachedResponse
{
    public function __construct(
        public readonly string $content,
        public readonly int $statusCode,
        public readonly array $headers
    ) {
    }
}
