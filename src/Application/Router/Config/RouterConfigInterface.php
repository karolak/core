<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router\Config;

interface RouterConfigInterface
{
    /**
     * @return array<string,array<int,string>>
     */
    public function getRoutes(): array;
}