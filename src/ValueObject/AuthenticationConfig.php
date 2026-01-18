<?php

namespace App\ValueObject;

final class AuthenticationConfig
{
    public static function fromArray(array $config): AuthenticationConfigInterface
    {
        $type = $config['type'];

        switch ($type) {
            case 'api_key':
                return ApiKeyAuthenticationConfig::fromArray($config);
            case 'basic':
                return BasicAuthenticationConfig::fromArray($config);
            case 'none':
                return NoAuthenticationConfig::fromArray($config);
            default:
                throw new \InvalidArgumentException("Unknown authentication type: {$type}");
        }
    }
}
