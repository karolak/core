<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

interface RouterInterface
{
    public const int HANDLER = 0;
    public const int PARAMETERS = 1;

    /**
     * @param string $method
     * @param string $path
     * @return list<string|array<string,string>>
     */
    public function dispatch(string $method, string $path): array;
}