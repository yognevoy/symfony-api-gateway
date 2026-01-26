<?php

namespace App\ValueObject;

use App\ValueObject\Auth\AuthenticationConfigInterface;
use App\ValueObject\Auth\ApiKeyAuthenticationConfig;
use App\ValueObject\Auth\BasicAuthenticationConfig;
use App\ValueObject\Auth\JwtAuthenticationConfig;
use App\ValueObject\Auth\NoAuthenticationConfig;

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
            case 'jwt':
                return JwtAuthenticationConfig::fromArray($config);
            case 'none':
                return NoAuthenticationConfig::fromArray($config);
            default:
                throw new \InvalidArgumentException("Unknown authentication type: {$type}");
        }
    }
}
