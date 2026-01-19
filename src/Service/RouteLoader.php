<?php

namespace App\Service;

use App\ValueObject\RouteConfig;
use App\ValueObject\RouteMatchResult;
use Symfony\Component\Yaml\Yaml;

/**
 * RouteLoader loads and manages API route configurations.
 *
 * This service is responsible for providing methods to retrieve route information based on path or name.
 */
class RouteLoader
{
    protected array $routes = [];

    public function __construct(
        protected readonly string $configPath
    )
    {
        $this->loadRoutes();
    }

    /**
     * Loads route configurations.
     *
     * @throws \RuntimeException If the configuration file is not found
     */
    protected function loadRoutes(): void
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

    public function getRouteByPath(string $path): ?RouteMatchResult
    {
        $routeConfig = $this->findRouteByPath($path);

        if ($routeConfig !== null) {
            $variables = $this->extractVariables($routeConfig->path, $path);
            return new RouteMatchResult($routeConfig, $variables);
        }

        return null;
    }

    /**
     * Finds a route configuration that matches the given path.
     *
     * @param string $path
     * @return RouteConfig|null
     */
    protected function findRouteByPath(string $path): ?RouteConfig
    {
        foreach ($this->routes as $routeConfig) {
            if ($this->matchRoute($routeConfig->path, $path)) {
                return $routeConfig;
            }
        }

        return null;
    }

    /**
     * Checks if a path matches a route pattern.
     *
     * @param string $pattern
     * @param string $path
     * @return bool
     */
    protected function matchRoute(string $pattern, string $path): bool
    {
        $regexPattern = preg_quote($pattern, '/');

        $regexPattern = preg_replace('/\\\{([^\/]+)\\\}/', '([^\/]+)', $regexPattern);

        $regexPattern = '/^' . $regexPattern . '$/';

        return preg_match($regexPattern, $path) === 1;
    }

    /**
     * Extracts variables from a path based on a pattern.
     *
     * @param string $pattern
     * @param string $path
     * @return array
     */
    protected function extractVariables(string $pattern, string $path): array
    {
        $regexPattern = preg_quote($pattern, '/');

        $regexPattern = preg_replace('/\\\{([^\/]+)\\\}/', '([^\/]+)', $regexPattern);

        $regexPattern = '/^' . $regexPattern . '$/';

        preg_match_all('/\{([^\/]+)}/', $pattern, $variableNames);
        $variableNames = $variableNames[1];

        if (preg_match($regexPattern, $path, $matches)) {
            array_shift($matches);

            $variables = [];
            foreach ($variableNames as $i => $name) {
                if (isset($matches[$i])) {
                    $variables[$name] = $matches[$i];
                }
            }

            return $variables;
        }

        return [];
    }

    /**
     * Substitutes variables in a target URL with actual values.
     *
     * @param string $target The target URL with placeholders (e.g., 'https://api.example.com/users/{id}/')
     * @param array $variables Array of variable names to values
     * @return string
     */
    public function substituteVariables(string $target, array $variables): string
    {
        $result = $target;

        foreach ($variables as $name => $value) {
            $result = str_replace('{' . $name . '}', $value, $result);
        }

        return $result;
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
