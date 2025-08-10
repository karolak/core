<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use DomainException;

abstract class AbstractAggregate implements EntityInterface
{
    /** @var array<int,EventInterface> */
    private array $events = [];

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

    /**
     * @param array<int,EventInterface> $events
     * @return static
     * @throws DomainException
     */
    abstract public static function reconstruct(array $events): static;
}