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

    public static function fromArray(array $config): self
    {
        return new self(
            $config['duration'] ?? self::DEFAULT_DURATION,
            $config['retries'] ?? self::DEFAULT_RETRIES,
            $config['retry_delay'] ?? self::DEFAULT_RETRY_DELAY
        );
    }

    public static function disabled(): array
    {
        return [
            'duration' => self::DEFAULT_DURATION,
            'retries' => self::DEFAULT_RETRIES,
            'retry_delay' => self::DEFAULT_RETRY_DELAY
        ];
    }
}
