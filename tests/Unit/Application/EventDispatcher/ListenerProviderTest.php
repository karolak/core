<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\EventDispatcher;

use Karolak\Core\Application\EventDispatcher\EventDispatcherConfigInterface;
use Karolak\Core\Application\EventDispatcher\ListenerClassifierInterface;
use Karolak\Core\Application\EventDispatcher\ListenerFor;
use Karolak\Core\Application\EventDispatcher\ListenerProvider;
use Karolak\Core\Tests\Mock\DummyEvent;
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
    public function testShouldProvideListenerForEvent(): void
    {
        // given
        $listener = new #[ListenerFor(DummyEvent::class)] class {
            public function __invoke(DummyEvent $event): void
            {
            }
        };

        $config = $this->createMock(EventDispatcherConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getListeners')
            ->willReturn([$listener::class]);

        $classifier = $this->createMock(ListenerClassifierInterface::class);
        $classifier
            ->expects($this->once())
            ->method('groupByEvents')
            ->with([$listener::class])
            ->willReturn([DummyEvent::class => [$listener::class]]);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with($listener::class)
            ->willReturn($listener);

        $logger = $this->createMock(LoggerInterface::class);

        // when
        $result = new ListenerProvider($config, $classifier, $container, $logger)
            ->getListenersForEvent(new DummyEvent());

        // then
        $this->assertCount(1, $result);
        foreach ($result as $item) {
            $this->assertInstanceOf($listener::class, $item);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldLogErrorWhenListenerNotInContainer(): void
    {
        // given
        $listener = new #[ListenerFor(DummyEvent::class)] class {};

        $config = $this->createMock(EventDispatcherConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getListeners')
            ->willReturn([$listener::class]);

        $classifier = $this->createMock(ListenerClassifierInterface::class);
        $classifier
            ->expects($this->once())
            ->method('groupByEvents')
            ->with([$listener::class])
            ->willReturn([DummyEvent::class => [$listener::class]]);

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
        $result = new ListenerProvider($config, $classifier, $container, $logger)
            ->getListenersForEvent(new DummyEvent());

        // then
        $this->assertCount(0, $result);
    }
}