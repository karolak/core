<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\EventDispatcher;

use ArrayIterator;
use Karolak\Core\Application\EventDispatcher\EventDispatcher;
use Karolak\Core\Tests\Mock\DummyEvent;
use Karolak\Core\Tests\Mock\DummyListener;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

#[CoversClass(EventDispatcher::class)]
final class EventDispatcherTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldDispatchEvent(): void
    {
        // given
        $event = new DummyEvent();
        $listener1 = $this->createMock(DummyListener::class);
        $listener2 = $this->createMock(DummyListener::class);
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // then
        $listener1
            ->expects($this->once())
            ->method('__invoke')
            ->with($event);

        $listener2
            ->expects($this->once())
            ->method('__invoke')
            ->with($event);

        $listenerProvider
            ->expects($this->once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn(new ArrayIterator([$listener1, $listener2]));

        // when
        new EventDispatcher($listenerProvider)->dispatch($event);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldStopPropagationBeforeGetsListeners(): void
    {
        // given
        $event = new class implements StoppableEventInterface {
            /**
             * @inheritDoc
             */
            #[Override]
            public function isPropagationStopped(): bool
            {
                return true;
            }
        };
        $listener1 = $this->createMock(DummyListener::class);
        $listener2 = $this->createMock(DummyListener::class);
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // then
        $listener1
            ->expects($this->never())
            ->method('__invoke')
            ->with($event);

        $listener2
            ->expects($this->never())
            ->method('__invoke')
            ->with($event);

        $listenerProvider
            ->expects($this->never())
            ->method('getListenersForEvent')
            ->with($event);

        // when
        new EventDispatcher($listenerProvider)->dispatch($event);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldStopPropagationAfterFirstListener(): void
    {
        // given
        $event = new class extends DummyEvent implements StoppableEventInterface {
            private bool $isPropagationStopped = false;

            /**
             * @inheritDoc
             */
            #[Override]
            public function isPropagationStopped(): bool
            {
                return $this->isPropagationStopped;
            }

            /**
             * @return void
             */
            public function stopPropagation(): void
            {
                $this->isPropagationStopped = true;
            }
        };
        $listener1 = $this->createMock(DummyListener::class);
        $listener2 = $this->createMock(DummyListener::class);
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // then
        $listener1
            ->expects($this->once())
            ->method('__invoke')
            ->with($event)
            ->willReturnCallback(function (StoppableEventInterface $event) {
                if (method_exists($event, 'stopPropagation')) {
                    $event->stopPropagation();
                }
            });

        $listener2
            ->expects($this->never())
            ->method('__invoke')
            ->with($event);

        $listenerProvider
            ->expects($this->once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn(new ArrayIterator([$listener1, $listener2]));

        // when
        new EventDispatcher($listenerProvider)->dispatch($event);
    }
}