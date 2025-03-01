<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Container;

use Karolak\Core\Application\Container\ContainerConfigInterface;
use Karolak\Core\Application\Container\ContainerEntryNotFoundException;
use Karolak\Core\Application\Container\ContainerException;
use Karolak\Core\Application\Container\LazyObjectContainer;
use Karolak\Core\Tests\Mock\EmptyInterface;
use Karolak\Core\Tests\Mock\EmptyObject;
use Karolak\Core\Tests\Mock\ObjectWithDependency;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[
    CoversClass(LazyObjectContainer::class),
    CoversClass(ContainerException::class),
    CoversClass(ContainerEntryNotFoundException::class)
]
final class LazyObjectContainerTest extends TestCase
{
    /**
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testShouldAddEntries(): void
    {
        // when
        $container = new LazyObjectContainer($this->getConfig([
            EmptyInterface::class => [EmptyObject::class],
            ObjectWithDependency::class => [ObjectWithDependency::class, EmptyInterface::class]
        ]));

        // then
        $this->assertTrue($container->has(EmptyInterface::class));
        $this->assertInstanceOf(EmptyObject::class, $container->get(EmptyInterface::class));
        $this->assertEquals(0, $container->get(EmptyInterface::class)->getValue());
        $this->assertTrue($container->has(ObjectWithDependency::class));
        $this->assertInstanceOf(ObjectWithDependency::class, $container->get(ObjectWithDependency::class));
        $this->assertEquals(0, $container->get(ObjectWithDependency::class)->object->getValue());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     */
    public function testShouldThrowExceptionOnIncorrectInitData(): void
    {
        // then
        $this->expectException(ContainerException::class);

        // when
        new LazyObjectContainer($this->getConfig([
            EmptyInterface::class => [EmptyInterface::class]
        ]));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShouldThrowExceptionWhenEntryNotFound(): void
    {
        // then
        $this->expectException(NotFoundExceptionInterface::class);

        // given
        $container = new LazyObjectContainer($this->getConfig());

        // when
        $container->get(EmptyObject::class);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShouldContainsItself(): void
    {
        // when
        $container = new LazyObjectContainer($this->getConfig());

        // then
        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertInstanceOf(LazyObjectContainer::class, $container->get(ContainerInterface::class));
    }

    /**
     * @param array<class-string,array<int,class-string>> $services
     * @return ContainerConfigInterface
     */
    private function getConfig(array $services = []): ContainerConfigInterface
    {
        return new readonly class($services) implements ContainerConfigInterface {
            /**
             * @param array<class-string,array<int,class-string>> $services
             */
            public function __construct(private array $services = []) {}

            /**
             * @return array<class-string,array<int,class-string>>
             */
            #[Override]
            public function getServices(): array
            {
                return $this->services;
            }
        };
    }
}