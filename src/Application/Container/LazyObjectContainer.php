<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container;

use Override;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Throwable;

final class LazyObjectContainer implements ContainerInterface
{
    /** @var array<class-string,object> */
    private array $container = [];

    /**
     * @param ContainerConfigInterface $config
     * @throws ContainerException
     */
    public function __construct(ContainerConfigInterface $config)
    {
        try {
            foreach ($config->getServices() as $id => $classes) {
                $this->container[$id] = new ReflectionClass($classes[0])
                    ->newLazyProxy(
                        function () use ($classes) {
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

            $this->container[ContainerInterface::class] = $this;
        } catch (Throwable $origin) {
            throw ContainerException::forInitializationWith($origin);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function get(string $id): object
    {
        return $this->container[$id] ?? throw ContainerEntryNotFoundException::forService($id);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}