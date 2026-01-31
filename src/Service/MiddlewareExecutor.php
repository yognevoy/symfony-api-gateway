<?php

namespace App\Service;

use App\Middleware\ConfigurableMiddlewareInterface;
use App\Middleware\LoggingMiddleware;
use App\Middleware\MiddlewareInterface;
use App\ValueObject\RouteConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MiddlewareExecutor executes the middleware stack for a given route.
 */
class MiddlewareExecutor
{
    public function __construct(
        private readonly MiddlewareRegistry $middlewareRegistry,
        private readonly ContainerInterface $container
    )
    {
    }

    /**
     * Execute the middleware stack for a route.
     *
     * @param Request $request The incoming request
     * @param RouteConfig $routeConfig The route configuration
     * @param callable $handler The handler to call after all middleware
     * @return Response
     */
    public function applyMiddleware(Request $request, RouteConfig $routeConfig, callable $handler): Response
    {
        $middlewares = array_merge(
            $this->getSystemMiddlewares($routeConfig),
            $this->getUserMiddlewares($routeConfig)
        );

        if (empty($middlewares)) {
            return $handler($request);
        }

        return $this->buildMiddlewareChain($middlewares, $handler)($request);
    }

    /**
     * Get system/core middlewares for the route.
     *
     * @param RouteConfig $routeConfig The route configuration
     * @return MiddlewareInterface[]
     */
    protected function getSystemMiddlewares(RouteConfig $routeConfig): array
    {
        $middlewares = [];

        if ($routeConfig->logging->enabled) {
            $loggingMiddleware = clone $this->container->get(LoggingMiddleware::class);

            if ($loggingMiddleware instanceof ConfigurableMiddlewareInterface) {
                $loggingMiddleware->configure($routeConfig->logging, $routeConfig->name);
            }

            $middlewares[] = $loggingMiddleware;
        }

        return $middlewares;
    }

    /**
     * Get user-defined middlewares for the route.
     *
     * @param RouteConfig $routeConfig The route configuration
     * @return MiddlewareInterface[]
     */
    protected function getUserMiddlewares(RouteConfig $routeConfig): array
    {
        if (empty($routeConfig->middleware)) {
            return [];
        }

        return $this->middlewareRegistry->getAll($routeConfig->middleware);
    }

    /**
     * Build the middleware chain by wrapping each middleware around the next.
     *
     * @param MiddlewareInterface[] $middlewares
     * @param callable $handler The final handler
     * @return callable
     */
    protected function buildMiddlewareChain(array $middlewares, callable $handler): callable
    {
        $chain = $handler;

        foreach (array_reverse($middlewares) as $middleware) {
            $chain = function (Request $request) use ($middleware, $chain) {
                return $middleware->process($request, $chain);
            };
        }

        return $chain;
    }
}
