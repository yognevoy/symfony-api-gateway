<?php

namespace App\Service;

use App\ValueObject\CachedResponse;
use App\ValueObject\RouteConfig;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheService
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache
    )
    {
    }

    /**
     * Retrieves a cached response from the cache.
     *
     * @param RouteConfig $routeConfig
     * @param Request $request
     * @return CachedResponse|null
     */
    public function get(RouteConfig $routeConfig, Request $request): ?CachedResponse
    {
        if (!$routeConfig->cache->isEnabled()) {
            return null;
        }

        $cacheKey = $this->generateCacheKey($routeConfig, $request);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            /** @var CachedResponse $cachedResponse */
            $cachedResponse = $cacheItem->get();
            return $cachedResponse;
        }

        return null;
    }

    /**
     * Stores a response in the cache.
     *
     * @param RouteConfig $routeConfig
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function set(RouteConfig $routeConfig, Request $request, Response $response): void
    {
        if (!$routeConfig->cache->isEnabled()) {
            return;
        }

        $cacheKey = $this->generateCacheKey($routeConfig, $request);
        $cacheItem = $this->cache->getItem($cacheKey);

        $cachedResponse = new CachedResponse(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );

        $cacheItem->set($cachedResponse);
        $cacheItem->expiresAfter($routeConfig->cache->ttl);
        $this->cache->save($cacheItem);
    }

    /**
     * Generates a unique cache key.
     *
     * @param RouteConfig $routeConfig
     * @param Request $request
     * @return string
     */
    private function generateCacheKey(RouteConfig $routeConfig, Request $request): string
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        $queryString = $request->getQueryString() ?: '';

        return 'cache_' . md5($routeConfig->name . $path . $method . $queryString);
    }
}
