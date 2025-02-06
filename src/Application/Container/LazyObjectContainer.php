<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container;

use Karolak\Core\Application\Container\Exception\ContainerEntryNotFoundException;
use Karolak\Core\Application\Container\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Throwable;

final class LazyObjectContainer implements ContainerInterface
{
    /** @var array<class-string,object> */
    private array $container = [];

    /**
     * @param array<class-string,array<int,class-string>> $services
     * @throws ContainerException
     */
    public function __construct(array $services = [])
    {
        try {
            foreach ($services as $id => $classes) {
                $this->container[$id] = new ReflectionClass($classes[0])
                    ->newLazyProxy(
                        function (object $o) use ($classes) {
                            $args = array_map(
                                function (string $class) {
                                    return $this->container[$class];
                                },
                                array_slice($classes, 1)
                            );

                            return new $classes[0](...$args);
                        }
                    );
            }
        } catch (Throwable $origin) {
            throw ContainerException::forInitializationWith($origin);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        return $this->container[$id] ?? throw ContainerEntryNotFoundException::forService($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}