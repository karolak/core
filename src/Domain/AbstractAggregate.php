<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

abstract class AbstractAggregate
{
    /** @var array<int,EventInterface> */
    private array $events = [];

    /**
     * @param array<int,EventInterface> $events
     * @return static
     * @throws NoEventsProvidedException
     * @throws MissingMethodException
     */
    public static function recreate(array $events): static
    {
        false === empty($events) ?: throw NoEventsProvidedException::occur();

        $createEvent = array_shift($events) ?? throw NoEventsProvidedException::occur();

        $namespace = explode('\\', get_class($createEvent));
        $methodName = 'createFrom' . array_pop($namespace);

        method_exists(static::class, $methodName) ?: throw MissingMethodException::occurFor($methodName);

        /** @var static $entity */
        $entity = static::$methodName($createEvent);
        $entity->releaseEvents();

        foreach ($events as $event) {
            $namespace = explode('\\', get_class($event));
            $methodName = 'apply' . array_pop($namespace);
            method_exists($entity, $methodName) ?: throw MissingMethodException::occurFor($methodName);
            $entity->$methodName($event);
        }

        return $entity;
    }

    /**
     * @return array<int,EventInterface>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    protected function recordEvent(EventInterface $event): void
    {
        $this->events[] = $event;
    }
}