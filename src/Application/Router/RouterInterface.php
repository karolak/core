<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

interface RouterInterface
{
    /**
     * @param string $method
     * @param string $path
     * @return list<string|array<string,string>>
     */
    public function dispatch(string $method, string $path): array;
}