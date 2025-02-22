<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Http;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class RequestHandler implements RequestHandlerInterface
{
    /** @var array<int|string,MiddlewareInterface> */
    private array $middlewares;

    /**
     * @param RequestHandlerInterface $defaultRequestHandler
     * @param MiddlewareInterface ...$middlewares
     */
    public function __construct(
        private RequestHandlerInterface $defaultRequestHandler,
        MiddlewareInterface ...$middlewares
    ) {
        $this->middlewares = $middlewares;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middlewares[0] ?? false;

        return $middleware
            ? $middleware->process($request, new self($this->defaultRequestHandler, ...array_slice($this->middlewares, 1)))
            : $this->defaultRequestHandler->handle($request);
    }
}
