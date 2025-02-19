<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Router;

use Karolak\Core\Application\Router\Router;
use Karolak\Core\Application\Router\RouterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(Router::class),
    CoversClass(Router::class)
]
final class RouterTest extends TestCase
{
    private const int ROUTE_NAME_KEY = 0;
    private const int ROUTE_ARGUMENTS_KEY = 1;

    /**
     * @return void
     */
    public function testItCanBeConstructed(): void
    {
        // when
        $router = new Router();

        // then
        $this->assertInstanceOf(Router::class, $router);
        $this->assertInstanceOf(RouterInterface::class, $router);
    }

    /**
     * @return void
     */
    public function testDispatchingSimpleRoutes(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/', 'GetPageRequestHandler'],
            'route2' => ['POST', '/a', 'AddPageRequestHandler'],
            'route3' => ['PUT', '/b', 'EditPageRequestHandler'],
            'route4' => ['DELETE', '/c', 'DeletePageRequestHandler']
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/');
        $d2 = $router->dispatch('POST', '/a');
        $d3 = $router->dispatch('PUT', '/b');
        $d4 = $router->dispatch('DELETE', '/c');

        // then
        $this->assertEquals('route1', $d1[self::ROUTE_NAME_KEY]);
        $this->assertEquals('route2', $d2[self::ROUTE_NAME_KEY]);
        $this->assertEquals('route3', $d3[self::ROUTE_NAME_KEY]);
        $this->assertEquals('route4', $d4[self::ROUTE_NAME_KEY]);
    }

    /**
     * @return void
     */
    public function testDispatchingParameterizedRoutes(): void
    {
        // given
        $routes = [
            'route1' => ['GET', '/page/{id:\d+}', 'GetPageRequestHandler'],
            'route2' => ['GET', '/user/{user_id:\d+}', 'GetUserRequestHandler'],
            'route3' => ['GET', '/user/{user_id:\d+}/address', 'GetUserAddressesRequestHandler'],
            'route4' => ['GET', '/user/{user_id:\d+}/address/{address_id:\d+}', 'GetUserAddressRequestHandler'],
            'route5' => ['GET', '/user/{user_id:\d+}/address/{address_id:\d+}/location', 'GetUserAddressLocationsRequestHandler'],
            'route6' => ['GET', '/user/{user_id:\d+}/address/{address_id:\d+}/location/{location_id:\d+}', 'GetUserAddressLocationRequestHandler'],
        ];
        $router = new Router($routes);

        // when
        $d1 = $router->dispatch('GET', '/page/123');
        $d2 = $router->dispatch('GET', '/user/123');
        $d3 = $router->dispatch('GET', '/user/123/address');
        $d4 = $router->dispatch('GET', '/user/123/address/21');
        $d5 = $router->dispatch('GET', '/user/123/address/21/location');
        $d6 = $router->dispatch('GET', '/user/123/address/21/location/34');

        // then
        $this->assertEquals('route1', $d1[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d1[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d1[self::ROUTE_ARGUMENTS_KEY]);

        $this->assertEquals('route2', $d2[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d2[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d2[self::ROUTE_ARGUMENTS_KEY]);

        $this->assertEquals('route3', $d3[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d3[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(1, $d3[self::ROUTE_ARGUMENTS_KEY]);

        $this->assertEquals('route4', $d4[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d4[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(2, $d4[self::ROUTE_ARGUMENTS_KEY]);

        $this->assertEquals('route5', $d5[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d5[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(2, $d5[self::ROUTE_ARGUMENTS_KEY]);

        $this->assertEquals('route6', $d6[self::ROUTE_NAME_KEY]);
        $this->assertIsArray($d6[self::ROUTE_ARGUMENTS_KEY]);
        $this->assertCount(3, $d6[self::ROUTE_ARGUMENTS_KEY]);
    }
}