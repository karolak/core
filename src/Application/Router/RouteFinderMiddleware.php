<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteFinderMiddleware implements MiddlewareInterface
{
    public const string HANDLER_ATTRIBUTE = 'route.handler';
    public const string PARAMETERS_ATTRIBUTE = 'route.parameters';

    /**
     * @param RouterInterface $router
     */
    public function __construct(private RouterInterface $router)
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
        $route = $this->router->dispatch(
            $request->getMethod(),
            rawurldecode($request->getUri()->getPath())
        );

        if (empty($route)) {
            return $handler->handle($request);
        }

        $request->withAttribute(self::HANDLER_ATTRIBUTE, $route[RouterInterface::HANDLER]);
        $request->withAttribute(self::PARAMETERS_ATTRIBUTE, $route[RouterInterface::PARAMETERS] ?? []);

        return $handler->handle($request);
    }
}