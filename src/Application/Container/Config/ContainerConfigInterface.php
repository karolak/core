<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container\Config;

interface ContainerConfigInterface
{
    /**
     * @return array<class-string,array<int,class-string>>
     */
    public function getServices(): array;
}