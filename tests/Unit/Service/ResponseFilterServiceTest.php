<?php

namespace App\Tests\Unit\Service;

use App\Service\ResponseFilterService;
use App\ValueObject\ResponseFilterConfig;
use PHPUnit\Framework\TestCase;

class ResponseFilterServiceTest extends TestCase
{
    private ResponseFilterService $responseFilterService;

    protected function setUp(): void
    {
        $this->responseFilterService = new ResponseFilterService();
    }

    public function testApplyFilterWithIncludeFields(): void
    {
        $content = json_encode([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ]);

        $filterConfig = new ResponseFilterConfig(
            include: ['id', 'name'],
            exclude: []
        );

        $result = $this->responseFilterService->applyFilter($content, $filterConfig);
        $filteredData = json_decode($result, true);

        $this->assertEquals([
            'id' => 1,
            'name' => 'John Doe'
        ], $filteredData);
        $this->assertArrayNotHasKey('email', $filteredData);
        $this->assertArrayNotHasKey('age', $filteredData);
    }

    public function testApplyFilterWithExcludeFields(): void
    {
        $content = json_encode([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ]);

        $filterConfig = new ResponseFilterConfig(
            include: [],
            exclude: ['email', 'age']
        );

        $result = $this->responseFilterService->applyFilter($content, $filterConfig);
        $filteredData = json_decode($result, true);

        $this->assertEquals([
            'id' => 1,
            'name' => 'John Doe'
        ], $filteredData);
        $this->assertArrayNotHasKey('email', $filteredData);
        $this->assertArrayNotHasKey('age', $filteredData);
    }

    public function testApplyFilterWithIncludeAndExcludeFields(): void
    {
        $content = json_encode([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'active' => true
        ]);

        $filterConfig = new ResponseFilterConfig(
            include: ['id', 'name', 'email', 'age'],
            exclude: ['age']
        );

        $result = $this->responseFilterService->applyFilter($content, $filterConfig);
        $filteredData = json_decode($result, true);

        $this->assertEquals([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ], $filteredData);
        $this->assertArrayNotHasKey('age', $filteredData);
        $this->assertArrayNotHasKey('active', $filteredData);
    }

    public function testApplyFilterReturnsOriginalContentForInvalidJson(): void
    {
        $content = '{invalid: json}';

        $filterConfig = new ResponseFilterConfig(
            include: ['id', 'name'],
            exclude: []
        );

        $result = $this->responseFilterService->applyFilter($content, $filterConfig);

        $this->assertEquals('{invalid: json}', $result);
    }

    public function testApplyFilterWithEmptyConfig(): void
    {
        $content = json_encode([
            'id' => 1,
            'name' => 'John Doe'
        ]);

        $filterConfig = new ResponseFilterConfig(
            include: [],
            exclude: []
        );

        $result = $this->responseFilterService->applyFilter($content, $filterConfig);
        $filteredData = json_decode($result, true);

        $this->assertEquals([
            'id' => 1,
            'name' => 'John Doe'
        ], $filteredData);
    }
}
