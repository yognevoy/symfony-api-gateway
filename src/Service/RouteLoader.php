<?php

namespace App\Service;

use App\ValueObject\RouteConfig;
use Symfony\Component\Yaml\Yaml;

/**
 * RouteLoader loads and manages API route configurations.
 *
 * This service is responsible for providing methods to retrieve route information based on path or name.
 */
class RouteLoader
{
    private array $routes = [];

    public function __construct(
        private readonly string $configPath
    )
    {
        $this->loadRoutes();
    }

    /**
     * Loads route configurations.
     *
     * @throws \RuntimeException If the configuration file is not found
     */
    private function loadRoutes(): void
    {
        if (!file_exists($this->configPath)) {
            throw new \RuntimeException(sprintf('Configuration file not found: %s', $this->configPath));
        }

        $config = Yaml::parseFile($this->configPath);

        if (isset($config['routes'])) {
            foreach ($config['routes'] as $key => $routeData) {
                $this->routes[$key] = RouteConfig::fromArray($routeData);
            }
        }
    }

    /**
     * Returns all loaded route configurations.
     *
     * @return array<RouteConfig>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Finds a route configuration by its path.
     *
     * @param string $path
     * @return RouteConfig|null
     */
    public function getRouteByPath(string $path): ?RouteConfig
    {
        foreach ($this->routes as $routeConfig) {
            if ($path === $routeConfig->path ||
                (str_starts_with($path, $routeConfig->path . '/'))) {
                return $routeConfig;
            }
        }

        return null;
    }

    /**
     * Finds a route configuration by its name.
     *
     * @param string $name
     * @return RouteConfig|null
     */
    public function getRouteByName(string $name): ?RouteConfig
    {
        return $this->routes[$name] ?? null;
    }
}
