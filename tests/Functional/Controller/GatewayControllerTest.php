<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GatewayControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cacheApp = static::getContainer()->get('cache.app');
        $cacheApp->clear();
    }

    public function testGetRequestToTestApiRoute(): void
    {
        $this->client->request('GET', '/test-api');

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertNotEmpty($content);
    }

    public function testPostRequestToTestApiPostRoute(): void
    {
        $postData = [
            'title' => 'Test Post',
            'body' => 'This is a test post body',
            'userId' => 1
        ];

        $this->client->request(
            'POST',
            '/test-api-post',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testGetRequestWithApiKeyAuthSuccess(): void
    {
        $this->client->request('GET', '/test-api-auth', [], [], [
            'HTTP_X_API_KEY' => 'test-key-123'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetRequestWithApiKeyAuthFailure(): void
    {
        $this->client->request('GET', '/test-api-auth', [], [], [
            'HTTP_X_API_KEY' => 'invalid-key'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetRequestWithApiKeyAuthMissingHeader(): void
    {
        $this->client->request('GET', '/test-api-auth');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetRequestWithPathVariable(): void
    {
        $this->client->request('GET', '/test-api-user/1');

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $content = json_decode($response->getContent(), true);
        if (isset($content['id'])) {
            $this->assertEquals(1, $content['id']);
        }
    }

    public function testGetRequestWithResponseFilter(): void
    {
        $this->client->request('GET', '/test-api-filter');

        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();

        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);

        if (!empty($content)) {
            $firstUser = $content[0];
            $this->assertArrayHasKey('id', $firstUser);
            $this->assertArrayHasKey('name', $firstUser);
            $this->assertArrayHasKey('email', $firstUser);

            $this->assertArrayNotHasKey('phone', $firstUser);
            $this->assertArrayNotHasKey('website', $firstUser);
        }
    }

    public function testRouteNotFound(): void
    {
        $this->client->request('GET', '/non-existent-route');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMethodNotAllowed(): void
    {
        $this->client->request('PATCH', '/test-api');

        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testRateLimitExceeded(): void
    {
        $this->client->request('GET', '/test-api-rate-limit');
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/test-api-rate-limit');
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/test-api-rate-limit');
        $this->assertResponseStatusCodeSame(Response::HTTP_TOO_MANY_REQUESTS);
    }
}
