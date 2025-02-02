<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Container;

use Karolak\Core\Application\Container\Exception\ContainerEntryNotFoundException;
use Karolak\Core\Application\Container\LazyObjectContainer;
use Karolak\Core\Tests\Mock\EmptyObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[
    UsesClass(EmptyObject::class),
    UsesClass(ContainerEntryNotFoundException::class),
    CoversClass(LazyObjectContainer::class),
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
    public function testShouldContainAddedEntry(): void
    {
        // given
        $services = [
            EmptyObject::class => static function (EmptyObject $containerEntry): void {
                $containerEntry->__construct();
            }
        ];

        // when
        $container = new LazyObjectContainer($services);

        // then
        $this->assertTrue($container->has(EmptyObject::class));
        $this->assertInstanceOf(EmptyObject::class, $container->get(EmptyObject::class));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShouldThrowExceptionWhenEntryNotFound(): void
    {
        // given
        $container = new LazyObjectContainer();

        // then
        $this->assertFalse($container->has(EmptyObject::class));
        $this->expectException(NotFoundExceptionInterface::class);

        // when
        $container->get(EmptyObject::class);
    }
}