<?php

namespace App\ValueObject;

/**
 * Base interface for authentication configuration objects.
 */
interface AuthenticationConfigInterface
{
    public function getType(): string;
}
