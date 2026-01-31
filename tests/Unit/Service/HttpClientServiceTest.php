<?php

namespace App\Tests\Unit\Service;

use App\Service\HttpClientService;
use App\ValueObject\TimeoutConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientServiceTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private HttpClientService $httpClientService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->httpClientService = new HttpClientService($this->httpClient);
    }

    public function testProxyRequest(): void
    {
        $targetUrl = 'https://api.example.com/users';
        $request = new Request();
        $request->server->set('HTTP_HOST', 'localhost');
        $request->setMethod('GET');

        $response = new MockResponse('OK', ['http_code' => 200]);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $targetUrl, ['headers' => []])
            ->willReturn($response);

        $result = $this->httpClientService->proxyRequest($targetUrl, $request);

        $this->assertSame($response, $result);
    }

    public function testProxyRequestWithBody(): void
    {
        $targetUrl = 'https://api.example.com/users';
        $requestContent = '{"name": "John Doe"}';
        $request = new Request();
        $request->initialize([], [], [], [], [], [], $requestContent);

        $response = new MockResponse('OK', ['http_code' => 200]);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $targetUrl, [
                'headers' => [],
                'body' => $requestContent,
            ])
            ->willReturn($response);

        $result = $this->httpClientService->proxyRequest($targetUrl, $request);

        $this->assertSame($response, $result);
    }

    public function testProxyRequestWithTimeout(): void
    {
        $targetUrl = 'https://api.example.com/users';
        $request = new Request();
        $timeoutConfig = new TimeoutConfig(duration: 30, retries: 0, retryDelay: 1000);

        $response = new MockResponse('OK', ['http_code' => 200]);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with('GET', $targetUrl, [
                'headers' => [],
                'timeout' => 30,
            ])
            ->willReturn($response);

        $result = $this->httpClientService->proxyRequest($targetUrl, $request, [], $timeoutConfig);

        $this->assertSame($response, $result);
    }
}
