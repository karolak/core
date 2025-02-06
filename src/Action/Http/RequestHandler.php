<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandler implements RequestHandlerInterface
{
    /**
     * @param RequestHandlerInterface $defaultRequestHandler
     * @param array<int,MiddlewareInterface> $middlewares
     */
    public function __construct(
        private readonly RequestHandlerInterface $defaultRequestHandler,
        private array $middlewares = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middlewares);

        return $middleware ?
            $middleware->process($request, new self($this->defaultRequestHandler, $this->middlewares))
            :
            $this->defaultRequestHandler->handle($request);
    }
}
