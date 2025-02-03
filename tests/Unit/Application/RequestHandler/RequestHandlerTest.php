<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\RequestHandler;

use Karolak\Core\Application\RequestHandler\RequestHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[
    UsesClass(RequestHandler::class),
    CoversClass(RequestHandler::class)
]
final class RequestHandlerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRunDefaultRequestHandlerWhenNoMiddlewares(): void
    {
        // given
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $defaultRequestHandler = $this->createMock(RequestHandlerInterface::class);

        // then
        $defaultRequestHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        // when
        new RequestHandler($defaultRequestHandler, [])->handle($request);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testMiddlewareShouldBeAbleToModifyResponse(): void
    {
        // given
        $originalResponseCode = 200;
        $modifiedResponseCode = 404;

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $defaultRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);

        $response
            ->method('getStatusCode')
            ->willReturn($originalResponseCode);
        $response
            ->method('withStatus')
            ->willReturnCallback(
                function (int $status, string $reasonPhrase = '') {
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($status);

                    return $response;
                });

        $defaultRequestHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($modifiedResponseCode) {
                    $response = $handler->handle($request);

                    return $response->withStatus($modifiedResponseCode);
                });

        // when
        $result = new RequestHandler($defaultRequestHandler, [$middleware1])->handle($request);

        // then
        $this->assertEquals($modifiedResponseCode, $result->getStatusCode());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldProcessEveryMiddleware(): void
    {
        // given
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $defaultRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $defaultRequestHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        // then
        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                });

        $middleware2
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                });

        // when
        new RequestHandler($defaultRequestHandler, [
            $middleware1,
            $middleware2
        ])->handle($request);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFirstMiddlewareShouldModifyResponseLast(): void
    {
        // given
        $startResponseCode = 200;
        $firstResponseCode = 201;
        $secondResponseCode = 202;

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $defaultRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $response
            ->method('getStatusCode')
            ->willReturn($startResponseCode);

        $defaultRequestHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($firstResponseCode) {
                    $handler->handle($request);
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($firstResponseCode);

                    return $response;
                });

        $middleware2
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($secondResponseCode) {
                    $handler->handle($request);
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($secondResponseCode);

                    return $response;
                });

        // when
        $result = new RequestHandler($defaultRequestHandler, [
            $middleware1,
            $middleware2
        ])->handle($request);

        // then
        $this->assertEquals($firstResponseCode, $result->getStatusCode());
    }
}
