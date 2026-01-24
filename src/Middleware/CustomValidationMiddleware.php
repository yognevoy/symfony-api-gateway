<?php

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomValidationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if ($request->headers->has('X-Custom-Validation')) {
            $validationHeaderValue = $request->headers->get('X-Custom-Validation');

            if ($validationHeaderValue !== 'valid') {
                return new Response(
                    json_encode(['error' => 'Invalid custom validation header']),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }
        }

        return $next($request);
    }
}
