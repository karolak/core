<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\EventDispatcher;

use ArrayIterator;
use Karolak\Core\Application\EventDispatcher\EventDispatcherConfigInterface;
use Karolak\Core\Application\EventDispatcher\ListenerProvider;
use Karolak\Core\Tests\Mock\DummyEvent;
use Karolak\Core\Tests\Mock\DummyListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(ListenerProvider::class)]
final class ListenerProviderTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldProvideListenersForEvent(): void
    {
        // given
        $event = new DummyEvent();
        $listener1 = $this->createMock(DummyListener::class);
        $listener2 = $this->createMock(DummyListener::class);

        $config = $this->createMock(EventDispatcherConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getListenersPerEvent')
            ->willReturn([
                $event::class => [
                    $listener1::class,
                    $listener2::class
                ]
            ]);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($listener1, $listener2);

        $logger = $this->createMock(LoggerInterface::class);

        // when
        /** @var ArrayIterator<int,callable> $result */
        $result = new ListenerProvider($config, $container, $logger)
            ->getListenersForEvent(new DummyEvent());

        // then
        $this->assertCount(2, $result);
        $result->rewind();
        $this->assertInstanceOf($listener1::class, $result->current());
        $result->next();
        $this->assertInstanceOf($listener2::class, $result->current());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldLogErrorWhenListenerNotInContainer(): void
    {
        // given
        $event = new DummyEvent();
        $listener = $this->createMock(DummyListener::class);

        $config = $this->createMock(EventDispatcherConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getListenersPerEvent')
            ->willReturn([
                $event::class => [
                    $listener::class
                ]
            ]);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with($listener::class)
            ->willThrowException($this->createMock(NotFoundExceptionInterface::class));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error');

        // when
        $result = new ListenerProvider($config, $container, $logger)
            ->getListenersForEvent(new DummyEvent());

        // then
        $this->assertCount(0, $result);
    }
}