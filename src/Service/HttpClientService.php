<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HttpClientService handles HTTP requests to external services.
 *
 * This service is responsible for forwarding requests to target APIs.
 */
class HttpClientService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    )
    {
    }

    /**
     * Proxies an HTTP request to a target URL.
     *
     * @param string $targetUrl The destination URL
     * @param Request $request The original incoming request
     * @param array $headers Additional headers to include in the request
     * @return ResponseInterface The response from the target service
     */
    public function proxyRequest(
        string  $targetUrl,
        Request $request,
        array   $headers = []
    ): ResponseInterface
    {
        $requestHeaders = array_merge(
            $headers,
            $request->headers->all()
        );

        $hopByHopHeaders = [
            'connection', 'upgrade', 'proxy-authenticate', 'proxy-authorization',
            'te', 'trailers', 'transfer-encoding', 'content-length', 'accept-encoding', 'host'
        ];

        foreach ($hopByHopHeaders as $header) {
            unset($requestHeaders[$header]);
        }

        $options = [
            'headers' => $requestHeaders,
        ];

        if (!empty($request->getContent())) {
            $options['body'] = $request->getContent();
        }

        return $this->httpClient->request(
            $request->getMethod(),
            $targetUrl,
            $options
        );
    }
}
