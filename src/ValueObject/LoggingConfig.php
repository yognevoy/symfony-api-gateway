<?php

namespace App\ValueObject;

final class LoggingConfig
{
    private const ALLOWED_TYPES = ['stream', 'syslog', 'file'];
    private const ALLOWED_LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    public function __construct(
        public readonly bool   $enabled,
        public readonly string $type,
        public readonly string $level
    )
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid logging type "%s". Allowed types are: %s',
                $type,
                implode(', ', self::ALLOWED_TYPES)
            ));
        }

        if (!in_array(strtolower($level), self::ALLOWED_LEVELS)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid logging level "%s". Allowed levels are: %s',
                $level,
                implode(', ', self::ALLOWED_LEVELS)
            ));
        }
    }

    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['enabled'] ?? false,
            $config['type'] ?? 'stream',
            $config['level'] ?? 'info'
        );
    }

    /**
     * Creates a disabled logging configuration.
     *
     * @return LoggingConfig
     */
    public static function disabled(): self
    {
        return new self(
            enabled: false,
            type: 'stream',
            level: 'info'
        );
    }

    /**
     * Checks if logging is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
