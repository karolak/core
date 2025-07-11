<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Router;

use Karolak\Core\Application\Router\RouteFinderMiddleware;
use Karolak\Core\Application\Router\RouterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(RouteFinderMiddleware::class)]
final class RouteFinderMiddlewareTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldAddRouteHandlerAndRouteParametersAttributesToRequest(): void
    {
        // given
        $router = $this->createMock(RouterInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routeFinderMiddleware = new RouteFinderMiddleware($router);

        // then
        $router
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(['RequestHandler']);
        $request
            ->expects($this->atLeastOnce())
            ->method('withAttribute');

        // when
        $routeFinderMiddleware->process($request, $requestHandler);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldDoNothingWithRequestWhenRouteNotFound(): void
    {
        // given
        $router = $this->createMock(RouterInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routeFinderMiddleware = new RouteFinderMiddleware($router);

        // then
        $router
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn([]);
        $request
            ->expects($this->never())
            ->method('withAttribute');

        // when
        $routeFinderMiddleware->process($request, $requestHandler);
    }
}