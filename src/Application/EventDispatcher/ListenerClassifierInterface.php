<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

interface ListenerClassifierInterface
{
    /**
     * @param array<int,class-string> $listeners
     * @return array<class-string,array<int,class-string>>
     */
    public function groupByEvents(array $listeners): array;
}