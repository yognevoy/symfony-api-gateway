<?php

namespace App\Service;

use App\Service\Auth\AuthenticatorInterface;
use App\ValueObject\AuthenticationConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * AuthenticationManager handles multiple authentication methods.
 */
class AuthenticationManager
{
    private array $authenticators;

    public function __construct(iterable $authenticators)
    {
        $this->authenticators = iterator_to_array($authenticators);
    }

    /**
     * Validates the authentication from the request.
     *
     * @param Request $request
     * @param AuthenticationConfig $config
     * @return bool True if authentication is valid, throws exception otherwise
     */
    public function validate(Request $request, AuthenticationConfig $config): bool
    {
        if ($config->type === 'none') {
            return true;
        }

        $authType = $config->type;

        foreach ($this->authenticators as $authenticator) {
            if ($this->supports($authenticator, $authType)) {
                return $authenticator->validate($request, $config);
            }
        }

        throw new UnauthorizedHttpException(null, "Unsupported authentication type: {$authType}");
    }

    /**
     * Checks if the authenticator supports the given type.
     *
     * @param AuthenticatorInterface $authenticator
     * @param string $type
     * @return bool True if the authenticator supports the type, false otherwise
     */
    private function supports(AuthenticatorInterface $authenticator, string $type): bool
    {
        $serviceName = (new \ReflectionClass($authenticator))->getShortName();

        switch ($type) {
            case 'api_key':
                return $serviceName === 'ApiKeyAuthenticator';
            case 'basic':
                return $serviceName === 'BasicAuthenticator';
            default:
                return false;
        }
    }
}
