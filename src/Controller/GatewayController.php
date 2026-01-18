<?php

namespace App\Controller;

use App\Exception\MethodNotAllowedException;
use App\Exception\RouteNotFoundException;
use App\Exception\TargetApiException;
use App\Service\AuthenticationManager;
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
        private readonly RouteLoader           $routeLoader,
        private readonly HttpClientService     $httpClientService,
        private readonly AuthenticationManager $authenticationManager
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

        $routeConfig = $this->routeLoader->getRouteByPath($path);

        if (!$routeConfig) {
            throw new RouteNotFoundException();
        }

        if (!in_array($request->getMethod(), $routeConfig->methods)) {
            throw new MethodNotAllowedException();
        }

        $this->authenticationManager->validate(
            $request,
            $routeConfig->authentication
        );

        $targetUrl = $routeConfig->target;

        try {
            $response = $this->httpClientService->proxyRequest($targetUrl, $request);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            $filteredHeaders = [];

            return new Response($content, $statusCode, $filteredHeaders);

        } catch (\Exception $e) {
            throw new TargetApiException(message: 'Failed to reach target API: ' . $e->getMessage());
        }
    }
}
