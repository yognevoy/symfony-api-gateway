<?php

namespace App\Service\Auth;

use App\ValueObject\AuthenticationConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for authentication services.
 */
interface AuthenticatorInterface
{
    /**
     * Validates the authentication from the request.
     *
     * @param Request $request
     * @param AuthenticationConfig $config
     * @return bool True if authentication is valid, throws exception otherwise
     */
    public function validate(Request $request, AuthenticationConfig $config): bool;
}
