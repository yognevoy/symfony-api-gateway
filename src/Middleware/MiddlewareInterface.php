<?php

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareInterface
{
    /**
     * Process the request and return a response.
     *
     * @param Request $request The incoming request
     * @param callable $next The next middleware in the chain
     * @return Response The response from the middleware chain
     */
    public function process(Request $request, callable $next): Response;
}
