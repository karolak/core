<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\EventDispatcher;

use Karolak\Core\Application\EventDispatcher\AttributeBasedListenerClassifier;
use Karolak\Core\Application\EventDispatcher\ListenerFor;
use Karolak\Core\Tests\Mock\DummyEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[
    CoversClass(AttributeBasedListenerClassifier::class),
    CoversClass(ListenerFor::class)
]
final class AttributeBasedListenerClassifierTest extends TestCase
{
    /**
     * @return void
     * @throws ReflectionException
     */
    public function testShouldAssignListenerToClassFromAttribute(): void
    {
        // given
        $listener = new #[ListenerFor(DummyEvent::class)] class {};

        // when
        $result = new AttributeBasedListenerClassifier()->groupByEvents([$listener::class]);

        // then
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(DummyEvent::class, $result);
        $this->assertCount(1, $result[DummyEvent::class]);
        $this->assertEquals($listener::class, $result[DummyEvent::class][0]);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testShouldPreserveListenersPositions(): void
    {
        // given
        $listener1 = new #[ListenerFor(DummyEvent::class)] class {};
        $listener2 = new #[ListenerFor(DummyEvent::class)] class {};

        // when
        $result = new AttributeBasedListenerClassifier()->groupByEvents([$listener1::class, $listener2::class]);

        // then
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(DummyEvent::class, $result);
        $this->assertCount(2, $result[DummyEvent::class]);
        $this->assertEquals($listener1::class, $result[DummyEvent::class][0]);
        $this->assertEquals($listener2::class, $result[DummyEvent::class][1]);
    }
}