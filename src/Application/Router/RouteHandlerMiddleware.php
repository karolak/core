<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final class RouteHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $service = $request->getAttribute(RouteFinderMiddleware::HANDLER_ATTRIBUTE);
        if (false === is_string($service)) {
            return $handler->handle($request);
        }

        return $this->getRequestHandler($service)->handle($request);
    }

    /**
     * @param string $key
     * @return RequestHandlerInterface
     */
    private function getRequestHandler(string $key): RequestHandlerInterface
    {
        try {
            $requestHandler = $this->container->get($key);
            if (false === ($requestHandler instanceof RequestHandlerInterface)) {
                throw new RuntimeException('Route handler have to implement PSR RequestHandlerInterface.');
            }

            return $requestHandler;
        } catch (NotFoundExceptionInterface) {
            throw new RuntimeException('Route handler not found in container.');
        } catch (ContainerExceptionInterface $e) {
            throw new RuntimeException('Container exception.', 500, $e);
        }
    }
}