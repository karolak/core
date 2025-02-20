<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Router;

use Karolak\Core\Application\Router\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(Router::class),
    CoversClass(Router::class)
]
final class RouterTest extends TestCase
{
    private const int ROUTE_HANDLER_KEY = 0;
    private const int ROUTE_ARGUMENTS_KEY = 1;

    /**
     * @return void
     */
    public function testShouldDispatchRouteWithCorrectMethodWhenPathsAreSame(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/', 'RequestHandler1'],
            'route2' => ['POST', '/', 'RequestHandler2']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/');
        $d2 = $router->dispatch('POST', '/');

        // then
        $this->assertEquals('RequestHandler1', $d1[self::ROUTE_HANDLER_KEY]);
        $this->assertEquals('RequestHandler2', $d2[self::ROUTE_HANDLER_KEY]);
    }

    /**
     * @return void
     */
    public function testShouldDispatchRouteWithCorrectPathWhenMethodsAreSame(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/a', 'RequestHandler1'],
            'route2' => ['GET', '/b', 'RequestHandler2']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/a');
        $d2 = $router->dispatch('GET', '/b');

        // then
        $this->assertEquals('RequestHandler1', $d1[self::ROUTE_HANDLER_KEY]);
        $this->assertEquals('RequestHandler2', $d2[self::ROUTE_HANDLER_KEY]);
    }

    /**
     * @return void
     */
    public function testShouldDispatchRouteWithSingleParameter(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/{id}', 'RequestHandler1'],
            'route2' => ['GET', '/a/{id}', 'RequestHandler2'],
            'route3' => ['GET', '/a/{id}/b', 'RequestHandler3']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/1');
        $d2 = $router->dispatch('GET', '/a/1');
        $d3 = $router->dispatch('GET', '/a/1/b');

        // then
        $this->assertEquals('RequestHandler1', $d1[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d1[self::ROUTE_ARGUMENTS_KEY]['id']);

        $this->assertEquals('RequestHandler2', $d2[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d2[self::ROUTE_ARGUMENTS_KEY]['id']);

        $this->assertEquals('RequestHandler3', $d3[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d3[self::ROUTE_ARGUMENTS_KEY]['id']);
    }

    /**
     * @return void
     */
    public function testShouldDispatchRouteWithMultipleParameters(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/{id}/{num}', 'RequestHandler1'],
            'route2' => ['GET', '/a/{id}/{num}', 'RequestHandler2'],
            'route3' => ['GET', '/a/{id}/b/{num}', 'RequestHandler3']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/1/2');
        $d2 = $router->dispatch('GET', '/a/1/2');
        $d3 = $router->dispatch('GET', '/a/1/b/2');

        // then
        $this->assertEquals('RequestHandler1', $d1[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(2, $d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('num', $d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d1[self::ROUTE_ARGUMENTS_KEY]['id']);
        $this->assertEquals('2', $d1[self::ROUTE_ARGUMENTS_KEY]['num']);

        $this->assertEquals('RequestHandler2', $d2[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(2, $d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('num', $d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d2[self::ROUTE_ARGUMENTS_KEY]['id']);
        $this->assertEquals('2', $d2[self::ROUTE_ARGUMENTS_KEY]['num']);

        $this->assertEquals('RequestHandler3', $d3[self::ROUTE_HANDLER_KEY]);
        $this->assertIsArray($d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(2, $d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('id', $d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertArrayHasKey('num', $d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertEquals('1', $d3[self::ROUTE_ARGUMENTS_KEY]['id']);
        $this->assertEquals('2', $d3[self::ROUTE_ARGUMENTS_KEY]['num']);
    }

    /**
     * @return void
     */
    public function testShouldReturnEmptyArrayWhereRouteNotFound(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/', 'RequestHandler1']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/a');

        // then
        $this->assertEmpty($d1);
    }

    /**
     * @return void
     */
    public function testShouldIgnoreSlashAtTheEndOfPath(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/a', 'RequestHandler1']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/a');
        $d2 = $router->dispatch('GET', '/a/');

        // then
        $this->assertNotEmpty($d1);
        $this->assertNotEmpty($d2);
    }

    /**
     * @return void
     */
    public function testShouldDispatchRouteWithParameterValidation(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/{id:\d+}', 'RequestHandler1'],
            'route2' => ['GET', '/{id:[a-z]+}', 'RequestHandler2']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/123');
        $d2 = $router->dispatch('GET', '/abc');

        // then
        $this->assertEquals('RequestHandler1', $d1[self::ROUTE_HANDLER_KEY]);
        $this->assertEquals('RequestHandler2', $d2[self::ROUTE_HANDLER_KEY]);
    }

    /**
     * @return void
     */
    public function testParameterShouldBeRequired(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/{id}', 'RequestHandler1']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/a');
        $d2 = $router->dispatch('GET', '/');

        // then
        $this->assertNotEmpty($d1);
        $this->assertEmpty($d2);
    }
}