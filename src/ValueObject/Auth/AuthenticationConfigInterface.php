<?php

namespace App\ValueObject\Auth;

/**
 * Base interface for authentication configuration objects.
 */
interface AuthenticationConfigInterface
{
    public function getType(): string;
}
