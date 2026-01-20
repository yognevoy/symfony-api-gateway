<?php

namespace App\Controller;

use App\Exception\MethodNotAllowedException;
use App\Exception\RateLimitExceededException;
use App\Exception\RouteNotFoundException;
use App\Exception\TargetApiException;
use App\Service\AuthenticationManager;
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
        private readonly RateLimiter           $rateLimiter
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

        $headers = [];

        if ($routeConfig->rateLimit->isEnabled()) {
            $rateLimitResult = $this->rateLimiter->checkRateLimit(
                $routeConfig,
                md5($routeConfig->path)
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
            $response = $this->httpClientService->proxyRequest(
                $targetUrl,
                $request
            );

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if (!$routeConfig->responseFilter->isEmpty()) {
                $content = $this->responseFilterService->applyFilter(
                    $content,
                    $routeConfig->responseFilter
                );
            }

            return new Response($content, $statusCode, $headers);

        } catch (\Exception $e) {
            throw new TargetApiException(message: 'Failed to reach target API: ' . $e->getMessage());
        }
    }
}
