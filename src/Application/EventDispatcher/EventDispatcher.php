<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @param ListenerProviderInterface $listenerProvider
     */
    public function __construct(private ListenerProviderInterface $listenerProvider)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function dispatch(object $event): object
    {
        /** @var iterable<callable> $listeners */
        $listeners = $this->listenerProvider->getListenersForEvent($event);
        foreach ($listeners as $listener) {
            $listener($event);
        }

        return $event;
    }
}