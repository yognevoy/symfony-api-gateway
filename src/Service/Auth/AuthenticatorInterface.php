<?php

namespace App\Service\Auth;

use App\ValueObject\AuthenticationConfigInterface;
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
     * @param AuthenticationConfigInterface $config
     * @return bool True if authentication is valid, throws exception otherwise
     */
    public function validate(Request $request, AuthenticationConfigInterface $config): bool;

    /**
     * Checks if the authenticator supports the given configuration.
     *
     * @param AuthenticationConfigInterface $config
     * @return bool True if the authenticator supports the configuration, false otherwise
     */
    public function supports(AuthenticationConfigInterface $config): bool;
}
