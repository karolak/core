<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Domain;

use Karolak\Core\Domain\AbstractAggregate;
use Karolak\Core\Domain\EventInterface;
use Karolak\Core\Domain\MissingMethodException;
use Karolak\Core\Domain\NoEventsProvidedException;
use Karolak\Core\Tests\Mock\EmptyEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(AbstractAggregate::class),
    CoversClass(NoEventsProvidedException::class),
    CoversClass(MissingMethodException::class)
]
final class AbstractAggregateTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldNotReturnAnyEventRecords(): void
    {
        // given
        $entity = new class() extends AbstractAggregate {};

        // when
        $events = $entity->releaseEvents();

        // then
        $this->assertCount(0, $events);
    }

    /**
     * @return void
     */
    public function testShouldRecordEventAndReturnIt(): void
    {
        // given
        $entity = new class() extends AbstractAggregate {
            public function __construct() {
                $this->recordEvent(new class() implements EventInterface {});
            }
        };

        // when
        $events = $entity->releaseEvents();

        // then
        $this->assertCount(1, $events);
    }

    /**
     * @return void
     * @throws MissingMethodException
     */
    public function testShouldReturnExceptionWhenTryRecreateEntityFromNoEvents(): void
    {
        // then
        $this->expectException(NoEventsProvidedException::class);

        // when
        AbstractAggregate::recreate([]);
    }

    /**
     * @return void
     * @throws NoEventsProvidedException
     */
    public function testShouldReturnExceptionWhenTryRecreateEntityWhenCreationMethodNotFound(): void
    {
        // then
        $this->expectException(MissingMethodException::class);

        // when
        AbstractAggregate::recreate([
            new EmptyEvent()
        ]);
    }

    /**
     * @return void
     * @throws MissingMethodException
     * @throws NoEventsProvidedException
     */
    public function testShouldRecreateEntityWithOnlyFirstCreateEvent(): void
    {
        // given
        $entity = new class() extends AbstractAggregate {
            public function __construct() {
                $this->recordEvent(new EmptyEvent());
            }

            /**
             * @param EmptyEvent $event
             * @return static
             */
            protected static function createFromEmptyEvent(EmptyEvent $event): static
            {
                return new static();
            }
        };

        // when
        $recreatedEntity = $entity::recreate([new EmptyEvent()]);

        // then
        $this->assertCount(0, $recreatedEntity->releaseEvents());
    }

    /**
     * @return void
     * @throws MissingMethodException
     * @throws NoEventsProvidedException
     */
    public function testShouldRecreateEntityWithTwoEvents(): void
    {
        // given
        $entity = new class() extends AbstractAggregate {
            public string $test = '';

            public function __construct() {
                $this->recordEvent(new EmptyEvent());
            }

            /**
             * @param EmptyEvent $event
             * @return static
             */
            protected static function createFromEmptyEvent(EmptyEvent $event): static
            {
                return new static();
            }

            /**
             * @param EmptyEvent $event
             * @return void
             */
            protected function applyEmptyEvent(EmptyEvent $event): void
            {
                $this->test = 'test';
            }
        };

        // when
        $recreatedEntity = $entity::recreate([
            new EmptyEvent(),
            new EmptyEvent()
        ]);

        // then
        $this->assertCount(0, $recreatedEntity->releaseEvents());
        $this->assertEquals('test', $recreatedEntity->test);
    }

    /**
     * @return void
     * @throws MissingMethodException
     * @throws NoEventsProvidedException
     */
    public function testShouldThrowExceptionWhenRecreateEntityWithTwoEventsWhenMissingApplyMethod(): void
    {
        // then
        $this->expectException(MissingMethodException::class);

        // given
        $entity = new class() extends AbstractAggregate {
            public function __construct() {
                $this->recordEvent(new EmptyEvent());
            }

            /**
             * @param EmptyEvent $event
             * @return static
             */
            protected static function createFromEmptyEvent(EmptyEvent $event): static
            {
                return new static();
            }
        };

        // when
        $entity::recreate([
            new EmptyEvent(),
            new EmptyEvent()
        ]);
    }
}