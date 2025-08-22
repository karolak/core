<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Infrastructure\Repository;

use DateTimeImmutable;
use Karolak\Core\Application\Database\DatabaseAbstractionLayerInterface;
use Karolak\Core\Domain\AbstractAggregate;
use Karolak\Core\Domain\EventInterface;
use Karolak\Core\Domain\IdInterface;
use Karolak\Core\Infrastructure\Repository\EventSourcingDatabaseTableRepository;
use Karolak\Core\Tests\Mock\DummyEvent;
use Karolak\Core\Tests\Mock\DummyId;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;

#[CoversClass(EventSourcingDatabaseTableRepository::class)]
final class EventSourcingDatabaseTableRepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReleaseEventsFromAggregateOnlyOnce(): void
    {
        // given
        $aggregate = $this->createMock(AbstractAggregate::class);

        // then
        $aggregate
            ->expects($this->once())
            ->method('releaseEvents');

        // when
        $this->getRepositoryImplementation(
            $this->createMock(DatabaseAbstractionLayerInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }


    /**
     * @return void
     * @throws Exception
     */
    public function testShouldPersistAggregateInOneTransaction(): void
    {
        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('beginTransaction');
        $dal
            ->expects($this->once())
            ->method('commitTransaction');
        $dal
            ->expects($this->never())
            ->method('rollbackTransaction');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldQueryForAggregateVersion(): void
    {
        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('fetch')
            ->with($this->stringStartsWith('SELECT COUNT(*) AS version FROM repository'));

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldStartCountingVersionsFromOne(): void
    {
        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);

        // then
        $dal
            ->method('fetch')
            ->with($this->stringStartsWith('SELECT COUNT(*) AS version FROM repository'))
            ->willReturn(['version' => 0]);
        $dal
            ->method('execute')
            ->with($this->anything(), $this->callback(function (array $data) {
                return $data[':version'] === 1;
            }));

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldIncrementVersionOnEveryEvent(): void
    {
        // given
        $events = [
            $this->createMock(EventInterface::class),
            $this->createMock(EventInterface::class)
        ];
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn($events);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);

        // then
        $dal
            ->method('fetch')
            ->with($this->stringStartsWith('SELECT COUNT(*) AS version FROM repository'))
            ->willReturn(['version' => 3]);
        $invokedCount = $this->exactly(count($events));
        $dal
            ->expects($invokedCount)
            ->method('execute')
            ->with($this->anything(), $this->callback(function (array $data) use ($invokedCount) {
                return $data[':version'] === 3 + $invokedCount->numberOfInvocations();
            }));

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldPersistEveryEvent(): void
    {
        // given
        $events = [
            $this->createMock(EventInterface::class),
            $this->createMock(EventInterface::class)
        ];
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn($events);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);

        // then
        $dal
            ->expects($this->exactly(count($events)))
            ->method('execute')
            ->with($this->stringStartsWith('INSERT INTO repository'));

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldDispatchAllEvents(): void
    {
        // given
        $events = [
            $this->createMock(EventInterface::class),
            $this->createMock(EventInterface::class)
        ];
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn($events);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $invokedCount = $this->exactly(count($events));
        $eventDispatcher
            ->expects($invokedCount)
            ->method('dispatch')
            ->willReturnCallback(function (EventInterface $event) use ($invokedCount, $events) {
                $this->assertSame($events[$invokedCount->numberOfInvocations() - 1], $event);
            });

        // when
        $this->getRepositoryImplementation(
            $this->createMock(DatabaseAbstractionLayerInterface::class),
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldDoNothingWhenPersistingAggregateWithNoEvents(): void
    {
        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $dal
            ->expects($this->never())
            ->method('beginTransaction');
        $dal
            ->expects($this->never())
            ->method('commitTransaction');
        $dal
            ->expects($this->never())
            ->method('rollbackTransaction');
        $dal
            ->expects($this->never())
            ->method('execute');

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenPersistAggregateCannotBeginTransaction(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $dal
            ->method('isInTransaction')
            ->willReturn(false);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('beginTransaction')
            ->willThrowException(new \Exception());
        $dal
            ->expects($this->never())
            ->method('commitTransaction');
        $dal
            ->expects($this->never())
            ->method('rollbackTransaction');
        $dal
            ->expects($this->never())
            ->method('execute');

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRollbackTransactionAndThrowExceptionWhenGetVersionFailed(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $dal
            ->method('isInTransaction')
            ->willReturn(true);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('fetch')
            ->with($this->stringStartsWith('SELECT COUNT(*) AS version FROM repository'))
            ->willThrowException(new \Exception());
        $dal
            ->expects($this->never())
            ->method('commitTransaction');
        $dal
            ->expects($this->once())
            ->method('rollbackTransaction');

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRollbackTransactionAndThrowExceptionWhenPersistAggregateCannotExecute(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $dal
            ->method('isInTransaction')
            ->willReturn(true);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('beginTransaction');
        $dal
            ->expects($this->never())
            ->method('commitTransaction');
        $dal
            ->expects($this->once())
            ->method('rollbackTransaction');
        $dal
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception());

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRollbackTransactionAndThrowExceptionWhenPersistAggregateCannotCommitTransaction(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $dal
            ->method('isInTransaction')
            ->willReturn(true);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('commitTransaction')
            ->willThrowException(new \Exception());
        $dal
            ->expects($this->once())
            ->method('rollbackTransaction');

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldLogErrorWhenCommitAndRollbackTransactionThrowException(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $aggregate = $this->createMock(AbstractAggregate::class);
        $aggregate
            ->method('releaseEvents')
            ->willReturn([$this->createMock(EventInterface::class)]);
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $dal
            ->method('isInTransaction')
            ->willReturn(true);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // then
        $dal
            ->expects($this->once())
            ->method('commitTransaction')
            ->willThrowException(new \Exception());
        $dal
            ->expects($this->once())
            ->method('rollbackTransaction')
            ->willThrowException(new \Exception());

        $logger
            ->expects($this->exactly(2))
            ->method('error');

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        // when
        $this->getRepositoryImplementation(
            $dal,
            $eventDispatcher,
            $logger,
            $aggregate::class
        )->persist($aggregate);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldGetAggregateInstance(): void
    {
        // given
        $id = new DummyId('1');
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $aggregate = new class() extends AbstractAggregate {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function reconstruct(array $events): static
            {
                return new self();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return new DummyId('1');
            }
        };

        // then
        $dal
            ->expects($this->once())
            ->method('fetchAll')
            ->with(
                $this->stringStartsWith('SELECT * FROM repository WHERE aggregate_id = :aggregate_id ORDER BY version'),
                $this->callback(function (array $data) use ($id) {
                    return $data[':aggregate_id'] === strval($id);
                })
            )
            ->willReturn([
                [
                    'aggregate_id' => strval($id),
                    'version' => 1,
                    'event_class' => DummyEvent::class,
                    'payload' => '{}',
                    'occurred_on' => (new DateTimeImmutable())->format(DATE_ATOM),
                ],
                [
                    'aggregate_id' => strval($id),
                    'version' => 2,
                    'event_class' => DummyEvent::class,
                    'payload' => '{}',
                    'occurred_on' => (new DateTimeImmutable())->format(DATE_ATOM),
                ]
            ]);

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->get($id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenGetAggregateInstanceAndEventNotExists(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $id = new DummyId('1');
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $aggregate = new class() extends AbstractAggregate {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function reconstruct(array $events): static
            {
                return new self();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return new DummyId('1');
            }
        };

        // then
        $dal
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'aggregate_id' => strval($id),
                    'version' => 1,
                    'event_class' => 'NotExistingClass',
                    'payload' => '{}',
                    'occurred_on' => (new DateTimeImmutable())->format(DATE_ATOM),
                ]
            ]);

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->get($id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenGetAggregateInstanceAndEventPayloadIsInvalid(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $id = new DummyId('1');
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $aggregate = new class() extends AbstractAggregate {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function reconstruct(array $events): static
            {
                return new self();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return new DummyId('1');
            }
        };

        // then
        $dal
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'aggregate_id' => strval($id),
                    'version' => 1,
                    'event_class' => DummyEvent::class,
                    'payload' => 'a',
                    'occurred_on' => (new DateTimeImmutable())->format(DATE_ATOM),
                ]
            ]);

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->get($id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenImplementationHasWrongAggregateClass(): void
    {
        $this->expectException(RuntimeException::class);

        // when
        $this->getRepositoryImplementation(
            $this->createMock(DatabaseAbstractionLayerInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            stdClass::class
        )->get(new DummyId('1'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenFetchingEventsFail(): void
    {
        $this->expectException(RuntimeException::class);

        // given
        $id = new DummyId('1');
        $dal = $this->createMock(DatabaseAbstractionLayerInterface::class);
        $aggregate = new class() extends AbstractAggregate {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function reconstruct(array $events): static
            {
                return new self();
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return new DummyId('1');
            }
        };

        // then
        $dal
            ->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new \Exception());

        // when
        $this->getRepositoryImplementation(
            $dal,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(LoggerInterface::class),
            $aggregate::class
        )->get($id);
    }

    /**
     * @param DatabaseAbstractionLayerInterface $dal
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @param class-string $aggregateClass
     * @return EventSourcingDatabaseTableRepository
     */
    private function getRepositoryImplementation(
        DatabaseAbstractionLayerInterface $dal,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        string $aggregateClass
    ): EventSourcingDatabaseTableRepository
    {
        return new readonly class(
                $dal,
                $eventDispatcher,
                $logger,
                $aggregateClass
            ) extends EventSourcingDatabaseTableRepository {
            /**
             * @param DatabaseAbstractionLayerInterface $dal
             * @param EventDispatcherInterface $eventDispatcher
             * @param LoggerInterface $logger
             * @param class-string $aggregateClass
             */
            public function __construct(
                DatabaseAbstractionLayerInterface $dal,
                EventDispatcherInterface $eventDispatcher,
                LoggerInterface $logger,
                private string $aggregateClass
            ) {
                parent::__construct($dal, $eventDispatcher, $logger);
            }

            /**
             * @inheritDoc
             */
            #[Override]
            protected function getAggregateClass(): string
            {
                return $this->aggregateClass;
            }

            /**
             * @inheritDoc
             */
            #[Override]
            protected function getTableName(): string
            {
                return 'repository';
            }
        };
    }
}