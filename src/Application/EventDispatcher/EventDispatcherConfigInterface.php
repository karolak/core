<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

interface EventDispatcherConfigInterface
{
    /**
     * @return array<class-string,array<int,class-string>>
     */
    public function getListenersPerEvent(): array;
}