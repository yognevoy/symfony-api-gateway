<?php

namespace App\ValueObject;

final class ResponseFilterConfig
{
    public function __construct(
        public readonly array $include,
        public readonly array $exclude
    ) {
    }

    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['include'] ?? [],
            $config['exclude'] ?? []
        );
    }

    /**
     * Creates a disabled response filter configuration.
     *
     * @return ResponseFilterConfig
     */
    public static function disabled(): self
    {
        return new self(
            include: [],
            exclude: []
        );
    }

    /**
     * Checks if the response filter is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->include) && empty($this->exclude);
    }
}
