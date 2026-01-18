<?php

namespace App\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * BasicAuthenticator handles HTTP Basic Authentication.
 */
class BasicAuthenticator implements AuthenticatorInterface
{
    /**
     * Validates the basic authentication from the request.
     *
     * @param Request $request
     * @param array $config
     * @return bool
     */
    public function validate(Request $request, array $config): bool
    {
        if (!isset($config['type']) || $config['type'] !== 'basic') {
            return true;
        }

        $authHeader = $request->headers->get('Authorization') ?: $request->headers->get('HTTP_AUTHORIZATION');

        if (!$authHeader || !preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
            throw new UnauthorizedHttpException('Basic', 'Invalid authorization header');
        }

        $credentials = base64_decode($matches[1]);
        if (!$credentials) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials format');
        }

        [$username, $password] = explode(':', $credentials, 2) + [null, null];

        if ($username === null || $password === null) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials format');
        }

        $validUsers = $config['users'] ?? [];

        foreach ($validUsers as $user) {
            if (
                isset($user['username'], $user['password']) &&
                $user['username'] === $username &&
                $user['password'] === $password
            ) {
                return true;
            }
        }

        throw new UnauthorizedHttpException('Basic', 'Invalid credentials');
    }
}
