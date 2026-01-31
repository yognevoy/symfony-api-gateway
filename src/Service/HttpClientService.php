<?php

namespace App\Service;

use App\ValueObject\TimeoutConfig;
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
    private const HOP_BY_HOP_HEADERS = [
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
     * @param TimeoutConfig|null $timeoutConfig Configuration for timeout and retries
     * @return ResponseInterface The response from the target service
     * @throws \Exception
     */
    public function proxyRequest(
        string         $targetUrl,
        Request        $request,
        array          $headers = [],
        ?TimeoutConfig $timeoutConfig = null
    ): ResponseInterface
    {
        $requestHeaders = $this->prepareHeaders($request, $headers);

        $options = [
            'headers' => $requestHeaders,
        ];

        if (!empty($request->getContent())) {
            $options['body'] = $request->getContent();
        }

        if ($timeoutConfig) {
            $options['timeout'] = $timeoutConfig->duration;
        }

        if ($timeoutConfig && $timeoutConfig->retries > 0) {
            return $this->makeRequestWithRetries(
                $targetUrl,
                $request->getMethod(),
                $options,
                $timeoutConfig
            );
        }

        return $this->makeRequest(
            $targetUrl,
            $request->getMethod(),
            $options
        );
    }

    /**
     * Makes a simple request.
     *
     * @param string $targetUrl
     * @param string $method
     * @param array $options
     * @return ResponseInterface
     */
    protected function makeRequest(
        string $targetUrl,
        string $method,
        array  $options
    ): ResponseInterface
    {
        return $this->httpClient->request($method, $targetUrl, $options);
    }

    /**
     * Makes a request with retry logic.
     *
     * @param string $targetUrl
     * @param string $method
     * @param array $options
     * @param TimeoutConfig $timeoutConfig
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function makeRequestWithRetries(
        string        $targetUrl,
        string        $method,
        array         $options,
        TimeoutConfig $timeoutConfig
    ): ResponseInterface
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= $timeoutConfig->retries; $attempt++) {
            try {
                return $this->makeRequest(
                    $targetUrl,
                    $method,
                    $options
                );
            } catch (\Exception $e) {
                $lastException = $e;

                if ($attempt === $timeoutConfig->retries) {
                    break;
                }

                usleep($timeoutConfig->retryDelay * 1000);
            }
        }

        throw $lastException;
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
