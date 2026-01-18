<?php

namespace App\Service;

use App\Service\Auth\AuthenticatorInterface;
use App\ValueObject\AuthenticationConfigInterface;
use App\ValueObject\NoAuthenticationConfig;
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
     * @param AuthenticationConfigInterface $config
     * @return bool True if authentication is valid, throws exception otherwise
     */
    public function validate(Request $request, AuthenticationConfigInterface $config): bool
    {
        if ($config instanceof NoAuthenticationConfig) {
            return true;
        }

        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($config)) {
                return $authenticator->validate($request, $config);
            }
        }

        throw new UnauthorizedHttpException(null, "Unsupported authentication type: {$config->getType()}");
    }
}
