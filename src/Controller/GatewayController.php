<?php

namespace App\Controller;

use App\Service\HttpClientService;
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
        private readonly RouteLoader $routeLoader,
        private readonly HttpClientService $httpClientService
    ) {
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

        $routeConfig = $this->routeLoader->getRouteByPath($path);

        if (!$routeConfig) {
            return new Response(
                json_encode(['error' => 'Route not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        if (!in_array($request->getMethod(), $routeConfig['methods'])) {
            return new Response(
                json_encode(['error' => 'Method not allowed']),
                405,
                ['Content-Type' => 'application/json']
            );
        }

        $targetUrl = $routeConfig['target'];

        try {
            $response = $this->httpClientService->proxyRequest($targetUrl, $request);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            $filteredHeaders = [];

            return new Response($content, $statusCode, $filteredHeaders);

        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'error' => 'Failed to reach target API',
                    'message' => $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }
}
