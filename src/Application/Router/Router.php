<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

use Karolak\Core\Application\Router\Config\RouterConfigInterface;
use Override;

final class Router implements RouterInterface
{
    private const int METHOD_KEY = 0;
    private const int PATH_KEY = 1;
    private const int HANDLER_KEY = 2;

    /** @var array<string,array<int,string>> */
    private array $routes;

    /**
     * @param RouterConfigInterface $config
     */
    public function __construct(RouterConfigInterface $config)
    {
        $this->routes = $config->getRoutes();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function dispatch(string $method, string $path): array
    {
        $path = '/' . trim($path, '/');
        $result = [];
        foreach ($this->routes as $route) {
            if (($route[self::METHOD_KEY] ?? null) !== $method || ($route[self::PATH_KEY] ?? null) === null) {
                continue;
            }

            if ($route[self::PATH_KEY] === $path) {
                return [$route[self::HANDLER_KEY], []];
            }

            $routeRegex = preg_replace_callback('/{\w+(:([^}]+))?}/', function ($matches) {
                return isset($matches[1]) ? '(' . $matches[2] . ')' : '([a-zA-Z0-9_-]+)';
            }, $route[self::PATH_KEY]) ?? '';

            if (preg_match('@^' . $routeRegex . '$@', $path, $routeParamsValues) !== 1) {
                continue;
            }

            array_shift($routeParamsValues);
            $routeParamsNames = [];
            if (preg_match_all('/{(\w+)(:[^}]+)?}/', $route[self::PATH_KEY], $names) >= 1) {
                $routeParamsNames = $names[1];
            }

            $result = [
                $route[self::HANDLER_KEY],
                count($routeParamsNames) === count($routeParamsValues)
                    ? array_combine($routeParamsNames, $routeParamsValues)
                    : []
            ];

            break;
        }

        return $result;
    }
}