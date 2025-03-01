<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

interface RouterConfigInterface
{
    /**
     * @return array<string,array<int,string>> List of routes definitions.
     */
    public function getRoutes(): array;
}