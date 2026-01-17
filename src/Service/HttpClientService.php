<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    )
    {
    }

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
