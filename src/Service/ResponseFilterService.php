<?php

namespace App\Service;

use App\ValueObject\ResponseFilterConfig;

/**
 * ResponseFilterService handles filtering of API response content.
 */
class ResponseFilterService
{
    /**
     * Applies response filtering based on the provided configuration.
     *
     * @param string $content The response content
     * @param ResponseFilterConfig $filterConfig The filter configuration
     * @return string The filtered response content
     */
    public function applyFilter(string $content, ResponseFilterConfig $filterConfig): string
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $content;
        }

        if (!empty($filterConfig->include)) {
            $data = $this->applyIncludeFilter($data, $filterConfig->include);
        }

        if (!empty($filterConfig->exclude)) {
            $data = $this->applyExcludeFilter($data, $filterConfig->exclude);
        }

        return json_encode($data);
    }

    /**
     * Applies include filter.
     *
     * @param mixed $data
     * @param array $includeFields
     * @return mixed
     */
    private function applyIncludeFilter(mixed $data, array $includeFields): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($includeFields as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }
        return $result;
    }

    /**
     * Applies exclude filter.
     *
     * @param mixed $data
     * @param array $excludeFields
     * @return mixed
     */
    private function applyExcludeFilter(mixed $data, array $excludeFields): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($excludeFields as $field) {
            unset($data[$field]);
        }
        return $data;
    }
}
