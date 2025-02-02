<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container;

use Closure;
use Karolak\Core\Application\Container\Exception\ContainerEntryNotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class LazyObjectContainer implements ContainerInterface
{
    /** @var array<class-string,object> */
    private array $container = [];

    /**
     * @param array<class-string,Closure> $services
     */
    public function __construct(array $services = [])
    {
        foreach ($services as $class => $initializer) {
            $this->container[$class] = new ReflectionClass($class)->newLazyGhost($initializer);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        return $this->container[$id] ?? throw ContainerEntryNotFoundException::occured();
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}