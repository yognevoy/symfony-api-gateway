<?php

namespace App\Service;

use App\Middleware\MiddlewareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * MiddlewareRegistry manages the registration and retrieval of middleware services.
 */
class MiddlewareRegistry
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * Get a middleware instance by its service ID.
     *
     * @param string $middlewareId The service ID of the middleware
     * @return MiddlewareInterface The middleware instance
     */
    public function get(string $middlewareId): MiddlewareInterface
    {
        if (!$this->container->has($middlewareId)) {
            throw new ServiceNotFoundException(sprintf('Service "%s" not found in container.', $middlewareId));
        }

        $instance = $this->container->get($middlewareId);

        if (!($instance instanceof MiddlewareInterface)) {
            throw new \InvalidArgumentException(
                sprintf('Service %s does not implement MiddlewareInterface', $middlewareId)
            );
        }

        return $instance;
    }

    /**
     * Get multiple middleware instances by their IDs.
     *
     * @param array $middlewareIds Array of middleware service IDs
     * @return MiddlewareInterface[] Array of middleware instances
     */
    public function getAll(array $middlewareIds): array
    {
        $middlewares = [];

        foreach ($middlewareIds as $middlewareId) {
            $middlewares[] = $this->get($middlewareId);
        }

        return $middlewares;
    }
}
