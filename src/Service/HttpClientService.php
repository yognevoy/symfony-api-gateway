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
    /**
     * Headers that should not be forwarded.
     */
    private const array HOP_BY_HOP_HEADERS = [
        'connection',
        'upgrade',
        'proxy-authenticate',
        'proxy-authorization',
        'te',
        'trailers',
        'transfer-encoding',
        'content-length',
        'accept-encoding',
        'host'
    ];

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
        $requestHeaders = $this->prepareHeaders($request, $headers);

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

    /**
     * Prepares the final set of headers for request.
     *
     * @param Request $request
     * @param array $headers
     * @return array
     */
    protected function prepareHeaders(Request $request, array $headers): array
    {
        $headers = array_merge($request->headers->all(), $headers);

        foreach (self::HOP_BY_HOP_HEADERS as $headerName) {
            unset($headers[$headerName]);
            unset($headers[strtolower($headerName)]);
        }

        return $headers;
    }
}
