<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use DomainException;

trait ReconstructAggregateTrait
{
    /**
     * @param array<int,EventInterface> $events
     * @return static
     * @throws DomainException
     */
    public static function reconstruct(array $events): static
    {
        $createEvent = array_shift($events) ?? new DomainException('No events provided.');

        $namespace = explode('\\', get_class($createEvent));
        $methodName = 'initFrom' . array_pop($namespace);
        method_exists(static::class, $methodName) ?: throw new DomainException('Missing factory method: ' . $methodName);

        /** @var static $aggregate */
        $aggregate = static::$methodName($createEvent);

        foreach ($events as $event) {
            $namespace = explode('\\', get_class($event));
            $methodName = 'apply' . array_pop($namespace);
            method_exists($aggregate, $methodName) ?: throw new DomainException('Missing apply method: ' . $methodName);
            $aggregate->$methodName($event);
        }

        return $aggregate;
    }
}