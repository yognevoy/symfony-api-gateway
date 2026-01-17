<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class RouteLoader
{
    private array $routes = [];

    public function __construct(
        private readonly string $configPath
    )
    {
        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        if (!file_exists($this->configPath)) {
            throw new \RuntimeException(sprintf('Configuration file not found: %s', $this->configPath));
        }

        $config = Yaml::parseFile($this->configPath);

        if (isset($config['routes'])) {
            $this->routes = $config['routes'];
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRouteByPath(string $path): ?array
    {
        foreach ($this->routes as $routeName => $routeConfig) {
            if ($path === $routeConfig['path'] ||
                (str_starts_with($path, $routeConfig['path'] . '/'))) {
                return $routeConfig;
            }
        }

        return null;
    }

    public function getRouteByName(string $name): ?array
    {
        return $this->routes[$name] ?? null;
    }
}
