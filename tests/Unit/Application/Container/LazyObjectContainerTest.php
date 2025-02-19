<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Container;

use Karolak\Core\Application\Container\Exception\ContainerEntryNotFoundException;
use Karolak\Core\Application\Container\Exception\ContainerException;
use Karolak\Core\Application\Container\LazyObjectContainer;
use Karolak\Core\Tests\Mock\EmptyInterface;
use Karolak\Core\Tests\Mock\EmptyObject;
use Karolak\Core\Tests\Mock\ObjectWithDependency;
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
     */
    public function testShouldCreateContainerInstance(): void
    {
        // when
        $container = new LazyObjectContainer();

        // then
        $this->assertInstanceOf(LazyObjectContainer::class, $container);
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testShouldAddEntries(): void
    {
        // given
        $services = [
            EmptyInterface::class => [EmptyObject::class],
            ObjectWithDependency::class => [ObjectWithDependency::class, EmptyInterface::class]
        ];

        // when
        $container = new LazyObjectContainer($services);

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
        new LazyObjectContainer([
            EmptyInterface::class => [EmptyInterface::class]
        ]);
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
        $container = new LazyObjectContainer();

        // when
        $container->get(EmptyObject::class);
    }
}