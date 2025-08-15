<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\EventDispatcher;

use ArrayIterator;
use Karolak\Core\Application\EventDispatcher\EventDispatcher;
use Karolak\Core\Tests\Mock\DummyEvent;
use Karolak\Core\Tests\Mock\DummyListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;

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
        $callableListener = $this->createMock(DummyListener::class);
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // then
        $callableListener
            ->expects($this->once())
            ->method('__invoke')
            ->with($event);

        $listenerProvider
            ->expects($this->once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn(new ArrayIterator([$callableListener]));

        // when
        new EventDispatcher($listenerProvider)->dispatch($event);
    }
}