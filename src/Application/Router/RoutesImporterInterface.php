<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Router;

interface RoutesImporterInterface
{
    /**
     * @return array<string,array<int,string>>
     */
    public function import(): array;
}