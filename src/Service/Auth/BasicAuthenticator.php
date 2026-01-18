<?php

namespace App\Service\Auth;

use App\Exception\Auth\BasicAuthenticationException;
use App\ValueObject\Auth\AuthenticationConfigInterface;
use App\ValueObject\Auth\BasicAuthenticationConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * BasicAuthenticator handles HTTP Basic Authentication.
 */
class BasicAuthenticator implements AuthenticatorInterface
{
    /**
     * Validates the basic authentication from the request.
     *
     * @param Request $request
     * @param AuthenticationConfigInterface $config
     * @return bool
     */
    public function validate(Request $request, AuthenticationConfigInterface $config): bool
    {
        if (!$config instanceof BasicAuthenticationConfig) {
            throw new \LogicException('Invalid configuration type passed to BasicAuthenticator');
        }

        $authHeader = $request->headers->get('Authorization') ?: $request->headers->get('HTTP_AUTHORIZATION');

        if (!$authHeader || !preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
            throw BasicAuthenticationException::missingCredentials();
        }

        $credentials = base64_decode($matches[1]);
        if (!$credentials) {
            throw BasicAuthenticationException::invalidCredentials();
        }

        [$username, $password] = explode(':', $credentials, 2) + [null, null];

        if ($username === null || $password === null) {
            throw BasicAuthenticationException::invalidCredentials();
        }

        $validUsers = $config->users;

        foreach ($validUsers as $user) {
            if (
                isset($user['username'], $user['password']) &&
                $user['username'] === $username &&
                $user['password'] === $password
            ) {
                return true;
            }
        }

        throw BasicAuthenticationException::invalidCredentials();
    }

    /**
     * Checks if this authenticator supports the given configuration.
     *
     * @param AuthenticationConfigInterface $config
     * @return bool
     */
    public function supports(AuthenticationConfigInterface $config): bool
    {
        return $config instanceof BasicAuthenticationConfig;
    }
}
