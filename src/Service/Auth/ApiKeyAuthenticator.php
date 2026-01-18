<?php

namespace App\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * ApiKeyAuthenticator handles API key authentication.
 */
class ApiKeyAuthenticator implements AuthenticatorInterface
{
    public const DEFAULT_HEADER = 'X-API-Key';

    /**
     * Validates the API key from the request.
     *
     * @param Request $request
     * @param array $config
     * @return bool
     */
    public function validate(Request $request, array $config): bool
    {
        if (!isset($config['type']) || $config['type'] !== 'api_key') {
            return true;
        }

        $headerName = $config['header'] ?? self::DEFAULT_HEADER;

        $apiKey = $request->headers->get($headerName);

        if (isset($config['prefix']) && $apiKey !== null) {
            $prefix = $config['prefix'];
            if (str_starts_with($apiKey, $prefix)) {
                $apiKey = substr($apiKey, strlen($prefix));
            } else {
                throw new UnauthorizedHttpException('API key', 'Invalid API key format');
            }
        }

        $validKeys = $config['keys'] ?? [];

        if ($apiKey === null || !in_array($apiKey, $validKeys)) {
            throw new UnauthorizedHttpException('API key', 'Invalid API key');
        }

        return true;
    }
}
