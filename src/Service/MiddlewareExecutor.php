<?php

namespace App\Service;

use App\Middleware\MiddlewareInterface;
use App\ValueObject\RouteConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MiddlewareExecutor executes the middleware stack for a given route.
 */
class MiddlewareExecutor
{
    public function __construct(
        private readonly MiddlewareRegistry $middlewareRegistry
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
        if (empty($routeConfig->middleware)) {
            return $handler($request);
        }

        $middlewares = $this->middlewareRegistry->getAll($routeConfig->middleware);
        return $this->buildMiddlewareChain($middlewares, $handler)($request);
    }

    /**
     * Build the middleware chain by wrapping each middleware around the next.
     *
     * @param MiddlewareInterface[] $middlewares
     * @param callable $handler The final handler
     * @return callable
     */
    private function buildMiddlewareChain(array $middlewares, callable $handler): callable
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
