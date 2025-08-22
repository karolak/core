<?php

declare(strict_types=1);

namespace Karolak\Core\Infrastructure\Repository;

use Exception;
use Karolak\Core\Application\Database\DatabaseAbstractionLayerInterface;
use Karolak\Core\Application\Database\DisconnectedException;
use Karolak\Core\Application\Database\StorageException;
use Karolak\Core\Application\Database\TransactionException;
use Karolak\Core\Domain\AbstractAggregate;
use Karolak\Core\Domain\EventInterface;
use Karolak\Core\Domain\IdInterface;
use Karolak\Core\Domain\RepositoryInterface;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract readonly class EventSourcingDatabaseTableRepository implements RepositoryInterface
{
    /**
     * @param DatabaseAbstractionLayerInterface $dal
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        private DatabaseAbstractionLayerInterface $dal,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function persist(AbstractAggregate $aggregate): void
    {
        $events = $aggregate->releaseEvents();
        if (empty($events)) {
            return;
        }

        $this->beginTransaction();

        $version = $this->getAggregateVersion(strval($aggregate->getId()));
        foreach ($events as $event) {
            $this->persistEvent($aggregate, $event, ++$version);
        }

        $this->commitTransaction();

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function get(IdInterface $id): AbstractAggregate
    {
        $aggregateClass = $this->getVerifiedAggregateClass();
        $data = $this->getAggregateEvents(strval($id));

        $events = [];
        foreach ($data as $item) {
            $events[] = $this->reconstructEvent($item);
        }

        return $aggregateClass::reconstruct($events);
    }

    /**
     * @return class-string
     */
    protected abstract function getAggregateClass(): string;

    /**
     * @return string
     */
    protected abstract function getTableName(): string;

    /**
     * @return class-string<AbstractAggregate>
     */
    private function getVerifiedAggregateClass(): string
    {
        $aggregateClass = $this->getAggregateClass();
        if (false === is_subclass_of($aggregateClass, AbstractAggregate::class)) {
            $message = sprintf(
                'Aggregate class "%s" is not subclass of "%s".',
                $aggregateClass,
                AbstractAggregate::class
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }

        return $aggregateClass;
    }

    /**
     * @param string $aggregateId
     * @return int
     * @throws RuntimeException
     */
    private function getAggregateVersion(string $aggregateId): int
    {
        try {
            $result = $this->dal->fetch(
                'SELECT COUNT(*) AS version FROM ' . $this->getTableName() . ' WHERE aggregate_id = :aggregate_id',
                [':aggregate_id' => $aggregateId]
            );

            return isset($result['version']) && is_int($result['version']) ? $result['version'] : 0;
        } catch (DisconnectedException | StorageException | Exception $e) {
            $this->rollbackTransaction();

            $message = sprintf(
                'Could not retrieve aggregate version of aggregate id "%s" because: %s - %s',
                $aggregateId,
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }

    /**
     * @param string $aggregateId
     * @return array<int,array<string,mixed>>
     */
    private function getAggregateEvents(string $aggregateId): array
    {
        try {
            return $this->dal->fetchAll(
                'SELECT * FROM ' . $this->getTableName() . ' WHERE aggregate_id = :aggregate_id ORDER BY version',
                [':aggregate_id' => $aggregateId]
            );
        } catch (DisconnectedException | StorageException | Exception $e) {
            $message = sprintf(
                'Could not retrieve aggregate events of aggregate id "%s" because: %s - %s',
                $aggregateId,
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }

    /**
     * @param AbstractAggregate $aggregate
     * @param EventInterface $event
     * @param int $version
     * @return void
     * @throws RuntimeException
     */
    private function persistEvent(AbstractAggregate $aggregate, EventInterface $event, int $version): void
    {
        try {
            $this->dal->execute(
                'INSERT INTO '
                . $this->getTableName()
                . ' (aggregate_id, version, event_class, payload, occurred_on) VALUES (:aggregate_id, :version, :event_class, :payload, :occurred_on)',
                [
                    ':aggregate_id' => strval($aggregate->getId()),
                    ':version' => $version,
                    ':event_class' => get_class($event),
                    ':payload' => json_encode($event->toPayload()),
                    ':occurred_on' => $event->occurredOn()->format(DATE_ATOM)
                ]
            );
        } catch (DisconnectedException | StorageException | Exception $e) {
            $this->rollbackTransaction();

            $message = sprintf(
                'Could not persist event of aggregate id "%s" because: %s - %s',
                strval($aggregate->getId()),
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }

    /**
     * @param array<string,mixed> $data
     * @return EventInterface
     */
    private function reconstructEvent(array $data): EventInterface
    {
        if (
            false === isset($data['event_class'])
            || false === is_string($data['event_class'])
            || false === is_subclass_of($data['event_class'], EventInterface::class)
        ) {
            throw new RuntimeException('Event class problem in ' . strval(json_encode($data)));
        }

        if (
            false === isset($data['payload'])
            || false === is_string($data['payload'])
            || false === json_validate($data['payload'])
        ) {
            throw new RuntimeException('Event payload problem in ' . strval(json_encode($data)));
        }

        return $data['event_class']::fromPayload((array) json_decode($data['payload'], true));
    }

    /**
     * @return void
     */
    private function beginTransaction(): void
    {
        try {
            $this->dal->beginTransaction();
        } catch (DisconnectedException | StorageException | TransactionException | Exception $e) {
            $message = sprintf(
                'Could not begin transaction because: %s - %s',
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }

    /**
     * @return void
     */
    private function commitTransaction(): void
    {
        try {
            $this->dal->commitTransaction();
        } catch (DisconnectedException | StorageException | TransactionException | Exception $e) {
            $this->rollbackTransaction();

            $message = sprintf(
                'Could not commit transaction because: %s - %s',
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }

    /**
     * @return void
     */
    private function rollbackTransaction(): void
    {
        try {
            if ($this->dal->isInTransaction()) {
                $this->dal->rollbackTransaction();
            }
        } catch (DisconnectedException | StorageException | TransactionException | Exception $e) {
            $message = sprintf(
                'Could not rollback transaction because: %s - %s',
                $e::class,
                $e->getMessage()
            );
            $this->logger->error($message);
        }
    }
}