<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container;

interface ContainerConfigInterface
{
    /**
     * @return array<class-string,array<int,class-string>> List of string class services.
     */
    public function getServices(): array;
}