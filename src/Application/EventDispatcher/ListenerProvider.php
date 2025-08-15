<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

use ArrayIterator;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;

final readonly class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @param EventDispatcherConfigInterface $config
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(
        private EventDispatcherConfigInterface $config,
        private ContainerInterface $container,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritDoc
     * @return iterable<callable>
     */
    #[Override]
    public function getListenersForEvent(object $event): iterable
    {
        $eventListeners = $this->config->getListenersPerEvent()[$event::class] ?? [];
        $result = [];
        foreach ($eventListeners as $eventListener) {
            try {
                /** @var callable|mixed $listener */
                $listener = $this->container->get($eventListener);
                if (is_callable($listener)) {
                    $result[] = $listener;
                }
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
                $this->logger->error($e);
            }
        }

        return new ArrayIterator($result);
    }
}