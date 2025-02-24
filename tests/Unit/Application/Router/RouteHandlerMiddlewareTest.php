<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Router;

use Karolak\Core\Application\Router\RouteFinderMiddleware;
use Karolak\Core\Application\Router\RouteHandlerMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use stdClass;

#[
    UsesClass(RouteHandlerMiddleware::class),
    CoversClass(RouteHandlerMiddleware::class)
]
final class RouteHandlerMiddlewareTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldHandleRequestFromContainer(): void
    {
        // given
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerFromContainer = $this->createMock(RequestHandlerInterface::class);
        $routeHandlerMiddleware = new RouteHandlerMiddleware($container);
        $response = $this->createMock(ResponseInterface::class);

        // then
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteFinderMiddleware::HANDLER_ATTRIBUTE)
            ->willReturn('RouteHandler');
        $requestHandler
            ->expects($this->never())
            ->method('handle')
            ->with($request);
        $requestHandlerFromContainer
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $container
            ->expects($this->once())
            ->method('get')
            ->willReturn($requestHandlerFromContainer);

        // when
        $routeHandlerMiddleware->process($request, $requestHandler);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRunDefaultHandlerWhenRouteNotFound(): void
    {
        // given
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routeHandlerMiddleware = new RouteHandlerMiddleware($container);

        // then
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteFinderMiddleware::HANDLER_ATTRIBUTE)
            ->willReturn(null);
        $requestHandler
            ->expects($this->once())
            ->method('handle')
            ->with($request);

        // when
        $routeHandlerMiddleware->process($request, $requestHandler);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowRuntimeExceptionWhenRouteHandlerNotFound(): void
    {
        // given
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $notFoundException = $this->createMock(NotFoundExceptionInterface::class);
        $routeHandlerMiddleware = new RouteHandlerMiddleware($container);

        // then
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteFinderMiddleware::HANDLER_ATTRIBUTE)
            ->willReturn('RouteHandlerNotFound');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('RouteHandlerNotFound')
            ->willThrowException($notFoundException);
        $this->expectException(RuntimeException::class);

        // when
        $routeHandlerMiddleware->process($request, $requestHandler);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowRuntimeExceptionWhenContainerExceptionOccurred(): void
    {
        // given
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $containerException = $this->createMock(ContainerExceptionInterface::class);
        $routeHandlerMiddleware = new RouteHandlerMiddleware($container);

        // then
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteFinderMiddleware::HANDLER_ATTRIBUTE)
            ->willReturn('RouteHandlerNotFound');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('RouteHandlerNotFound')
            ->willThrowException($containerException);
        $this->expectException(RuntimeException::class);

        // when
        $routeHandlerMiddleware->process($request, $requestHandler);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowRuntimeExceptionWhenRouteHandlerNotImplementRequestHandlerInterface(): void
    {
        // given
        $container = $this->createMock(ContainerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routeHandlerMiddleware = new RouteHandlerMiddleware($container);

        // then
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(RouteFinderMiddleware::HANDLER_ATTRIBUTE)
            ->willReturn('RouteHandler');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('RouteHandler')
            ->willReturn(new stdClass());
        $this->expectException(RuntimeException::class);

        // when
        $routeHandlerMiddleware->process($request, $requestHandler);
    }
}