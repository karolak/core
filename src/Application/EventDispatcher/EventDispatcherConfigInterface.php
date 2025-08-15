<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

interface EventDispatcherConfigInterface
{
    /**
     * @return array<int,class-string>
     */
    public function getListeners(): array;
}