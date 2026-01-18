<?php

namespace App\Service\Auth;

use App\Exception\Auth\ApiKeyAuthenticationException;
use App\ValueObject\Auth\ApiKeyAuthenticationConfig;
use App\ValueObject\Auth\AuthenticationConfigInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @param AuthenticationConfigInterface $config
     * @return bool
     */
    public function validate(Request $request, AuthenticationConfigInterface $config): bool
    {
        if (!$config instanceof ApiKeyAuthenticationConfig) {
            throw new \LogicException('Invalid configuration type passed to ApiKeyAuthenticator');
        }

        $headerName = $config->header ?? self::DEFAULT_HEADER;

        $apiKey = $request->headers->get($headerName);

        if ($config->prefix !== null && $apiKey !== null) {
            $apiKey = $this->normalizeApiKey($apiKey, $config->prefix);
        }

        $validKeys = $config->keys;

        if ($apiKey === null || !in_array($apiKey, $validKeys)) {
            throw ApiKeyAuthenticationException::invalidApiKey();
        }

        return true;
    }

    /**
     * Normalizes the API key by removing the specified prefix.
     *
     * @param string $apiKey
     * @param string $prefix
     * @return string
     */
    protected function normalizeApiKey(string $apiKey, string $prefix): string
    {
        if (str_starts_with($apiKey, $prefix)) {
            return substr($apiKey, strlen($prefix));
        } else {
            throw ApiKeyAuthenticationException::invalidFormat();
        }
    }

    /**
     * Checks if this authenticator supports the given configuration.
     *
     * @param AuthenticationConfigInterface $config
     * @return bool
     */
    public function supports(AuthenticationConfigInterface $config): bool
    {
        return $config instanceof ApiKeyAuthenticationConfig;
    }
}
