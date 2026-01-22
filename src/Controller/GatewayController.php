<?php

namespace App\Controller;

use App\Exception\MethodNotAllowedException;
use App\Exception\RateLimitExceededException;
use App\Exception\RouteNotFoundException;
use App\Exception\TargetApiException;
use App\Service\AuthenticationManager;
use App\Service\CacheService;
use App\Service\HttpClientService;
use App\Service\RateLimiter;
use App\Service\ResponseFilterService;
use App\Service\RouteLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * GatewayController handles API requests and routes them to appropriate backend services.
 *
 * This controller acts as a reverse proxy, forwarding requests to configured target APIs
 * based on path patterns defined in the route configuration.
 */
class GatewayController extends AbstractController
{
    public function __construct(
        private readonly RouteLoader           $routeLoader,
        private readonly HttpClientService     $httpClientService,
        private readonly AuthenticationManager $authenticationManager,
        private readonly ResponseFilterService $responseFilterService,
        private readonly RateLimiter           $rateLimiter,
        private readonly CacheService          $cacheService
    )
    {
    }

    /**
     * Proxy method forwards incoming requests to backend services.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/{path}', name: 'api_gateway_proxy', requirements: ['path' => '.+'], methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    #[Route('/', name: 'api_gateway_root', methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    public function proxy(Request $request): Response
    {
        $path = $request->getPathInfo();

        $routeMatch = $this->routeLoader->getRouteByPath($path);

        if (!$routeMatch) {
            throw new RouteNotFoundException();
        }

        $routeConfig = $routeMatch->route;
        $variables = $routeMatch->variables;

        if (!in_array($request->getMethod(), $routeConfig->methods)) {
            throw new MethodNotAllowedException();
        }

        $cachedResponse = $this->cacheService->get($routeConfig, $request);
        if ($cachedResponse) {
            return new Response(
                $cachedResponse->content,
                $cachedResponse->statusCode,
                $cachedResponse->headers
            );
        }

        $headers = [];

        if ($routeConfig->rateLimit->isEnabled()) {
            $identifier = $routeConfig->rateLimit->perClient
                ? $request->getClientIp()
                : md5($routeConfig->path);

            $rateLimitResult = $this->rateLimiter->checkRateLimit(
                $routeConfig,
                $identifier
            );

            if ($rateLimitResult->isLimited()) {
                throw new RateLimitExceededException();
            }

            $headers = [
                'X-RateLimit-Limit' => $rateLimitResult->limit,
                'X-RateLimit-Remaining' => $rateLimitResult->remaining,
                'X-RateLimit-Used' => $rateLimitResult->used,
                'X-RateLimit-Reset' => $rateLimitResult->reset
            ];
        }

        $this->authenticationManager->validate(
            $request,
            $routeConfig->authentication
        );

        $targetUrl = $this->routeLoader->substituteVariables(
            $routeConfig->target,
            $variables
        );

        try {
            $proxyResponse = $this->httpClientService->proxyRequest(
                $targetUrl,
                $request,
                [],
                $routeConfig->timeout
            );

            $statusCode = $proxyResponse->getStatusCode();
            $content = $proxyResponse->getContent(false);

            if (!$routeConfig->responseFilter->isEmpty()) {
                $content = $this->responseFilterService->applyFilter(
                    $content,
                    $routeConfig->responseFilter
                );
            }

            $response = new Response($content, $statusCode, $headers);

            if ($routeConfig->cache->isEnabled()) {
                $this->cacheService->set($routeConfig, $request, $response);
            }

            return $response;

        } catch (\Exception $e) {
            throw new TargetApiException(message: 'Failed to reach target API: ' . $e->getMessage());
        }
    }
}
