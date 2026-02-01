<?php

namespace App\Tests\Unit\Service;

use App\Service\RouteLoader;
use App\ValueObject\RouteConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class RouteLoaderTest extends TestCase
{
    private string $configFilePath;
    private RouteLoader $routeLoader;

    protected function setUp(): void
    {
        $configData = [
            'routes' => [
                'users_list' => [
                    'path' => '/users',
                    'target' => 'https://api.example.com/users',
                    'methods' => ['GET'],
                ],
                'user_detail' => [
                    'path' => '/users/{id}',
                    'target' => 'https://api.example.com/users/{id}',
                    'methods' => ['GET', 'PUT', 'DELETE'],
                ],
                'posts_list' => [
                    'path' => '/posts',
                    'target' => 'https://api.example.com/posts',
                    'methods' => ['GET', 'POST'],
                ],
            ]
        ];

        $this->configFilePath = tempnam(sys_get_temp_dir(), 'route_config_test_');
        file_put_contents($this->configFilePath, Yaml::dump($configData));

        $this->routeLoader = new RouteLoader($this->configFilePath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->configFilePath)) {
            unlink($this->configFilePath);
        }
    }

    public function testGetRoutesReturnsRoutesFromFile(): void
    {
        $routes = $this->routeLoader->getRoutes();

        $this->assertCount(3, $routes);
        $this->assertArrayHasKey('users_list', $routes);
        $this->assertArrayHasKey('user_detail', $routes);
        $this->assertArrayHasKey('posts_list', $routes);

        $this->assertInstanceOf(RouteConfig::class, $routes['users_list']);
        $this->assertEquals('/users', $routes['users_list']->path);
        $this->assertEquals('https://api.example.com/users', $routes['users_list']->target);
    }

    public function testGetRouteByPathReturnsCorrectRoute(): void
    {
        $result = $this->routeLoader->getRouteByPath('/users');

        $this->assertNotNull($result);
        $this->assertEquals('users_list', $result->route->name);
        $this->assertEquals('/users', $result->route->path);
        $this->assertEmpty($result->variables);
    }

    public function testGetRouteByPathReturnsRouteWithVariables(): void
    {
        $result = $this->routeLoader->getRouteByPath('/users/123');

        $this->assertNotNull($result);
        $this->assertEquals('user_detail', $result->route->name);
        $this->assertEquals('/users/{id}', $result->route->path);
        $this->assertEquals(['id' => '123'], $result->variables);
    }

    public function testGetRouteByPathReturnsNullForNonMatchingPath(): void
    {
        $result = $this->routeLoader->getRouteByPath('/nonexistent');

        $this->assertNull($result);
    }

    public function testSubstituteVariablesReplacesPlaceholders(): void
    {
        $target = 'https://api.example.com/users/{id}/posts/{postId}';
        $variables = [
            'id' => '123',
            'postId' => '456'
        ];

        $result = $this->routeLoader->substituteVariables($target, $variables);

        $this->assertEquals('https://api.example.com/users/123/posts/456', $result);
    }

    public function testSubstituteVariablesHandlesMissingVariables(): void
    {
        $target = 'https://api.example.com/users/{id}/posts/{postId}';
        $variables = [
            'id' => '123'
            // postId is missing
        ];

        $result = $this->routeLoader->substituteVariables($target, $variables);

        $this->assertEquals('https://api.example.com/users/123/posts/{postId}', $result);
    }

    public function testGetRouteByNameReturnsCorrectRoute(): void
    {
        $route = $this->routeLoader->getRouteByName('users_list');

        $this->assertNotNull($route);
        $this->assertEquals('users_list', $route->name);
        $this->assertEquals('/users', $route->path);
    }

    public function testGetRouteByNameReturnsNullForNonExistentRoute(): void
    {
        $route = $this->routeLoader->getRouteByName('nonexistent_route');

        $this->assertNull($route);
    }

    public function testConstructorThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Configuration file not found:');

        new RouteLoader('/nonexistent/path/config.yaml');
    }
}
