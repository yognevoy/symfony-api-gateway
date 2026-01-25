<?php

namespace App\Middleware;

interface ConfigurableMiddlewareInterface
{
    /**
     * Configure the middleware with the provided configuration.
     *
     * @param mixed ...$params Configuration parameters
     */
    public function configure(mixed ...$params): void;
}
