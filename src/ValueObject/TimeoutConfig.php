<?php

namespace App\ValueObject;

final class TimeoutConfig
{
    public const DEFAULT_DURATION = 30;
    public const DEFAULT_RETRIES = 0;
    public const DEFAULT_RETRY_DELAY = 1000;

    public function __construct(
        public readonly int $duration,
        public readonly int $retries,
        public readonly int $retryDelay
    )
    {
    }

    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['duration'] ?? self::DEFAULT_DURATION,
            $config['retries'] ?? self::DEFAULT_RETRIES,
            $config['retry_delay'] ?? self::DEFAULT_RETRY_DELAY
        );
    }

    /**
     * Creates a disabled timeout configuration.
     *
     * @return TimeoutConfig
     */
    public static function disabled(): self
    {
        return new self(
            duration: self::DEFAULT_DURATION,
            retries: self::DEFAULT_RETRIES,
            retryDelay: self::DEFAULT_RETRY_DELAY
        );
    }
}
