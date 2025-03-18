<?php

declare(strict_types=1);

namespace Karolak\Core\Infrastructure\Database;

use Karolak\Core\Application\Database\DatabaseAbstractionLayerInterface;
use Karolak\Core\Application\Database\DisconnectedException;
use Karolak\Core\Application\Database\PDOFactoryInterface;
use Karolak\Core\Application\Database\StorageException;
use Karolak\Core\Application\Database\TransactionException;
use Override;
use PDO;
use PDOException;
use PDOStatement;

final class PDODatabaseAbstractionLayer implements DatabaseAbstractionLayerInterface
{
    /** @var PDO|null */
    private ?PDO $pdo;

    /** @var int */
    private int $transactionDepth = 0;

    /**
     * @param PDOFactoryInterface $factory
     * @throws StorageException
     */
    public function __construct(private readonly PDOFactoryInterface $factory)
    {
        $this->pdo = $this->factory->create();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function execute(string $sql, array $bindParams = []): void
    {
        $stmt = $this->prepareStatement($sql, $bindParams);
        $this->executeStatement($stmt, $sql, $bindParams);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function fetch(string $sql, array $bindParams = []): array
    {
        $stmt = $this->prepareStatement($sql, $bindParams);
        $this->executeStatement($stmt, $sql, $bindParams);

        /** @var array<string,mixed>|false $result */
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === is_array($result)) {
            throw StorageException::occur(
                sprintf(
                    'Could not fetch result from sql: %s --- with parameters: %s.',
                    $sql,
                    (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                )
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function fetchAll(string $sql, array $bindParams = []): array
    {
        try {
            $stmt = $this->prepareStatement($sql, $bindParams);
            $this->executeStatement($stmt, $sql, $bindParams);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw StorageException::occur(
                sprintf(
                    'Could not fetch result from sql: %s --- with parameters: %s.',
                    $sql,
                    (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                ),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function beginTransaction(): void
    {
        if (null === $this->pdo) {
            throw DisconnectedException::occur();
        }

        if ($this->transactionDepth > 0) {
            $this->execute('SAVEPOINT level' . $this->transactionDepth);
            $this->transactionDepth++;

            return;
        }

        try {
            $this->pdo->beginTransaction() ?: throw TransactionException::occurAtBegin();
            $this->transactionDepth++;
        } catch (PDOException $e) {
            throw TransactionException::occurAtBegin($e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function commitTransaction(): void
    {
        if (null === $this->pdo) {
            throw DisconnectedException::occur();
        }

        if ($this->transactionDepth === 0) {
            throw TransactionException::occurAtCommit();
        }

        $this->transactionDepth--;

        if ($this->transactionDepth > 0) {
            $this->execute('RELEASE SAVEPOINT level' . $this->transactionDepth);

            return;
        }

        try {
            $this->pdo->commit() ?: throw TransactionException::occurAtCommit();
        } catch (PDOException $e) {
            throw TransactionException::occurAtCommit($e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function rollbackTransaction(): void
    {
        if (null === $this->pdo) {
            throw DisconnectedException::occur();
        }

        if ($this->transactionDepth === 0) {
            throw TransactionException::occurAtRollback();
        }

        $this->transactionDepth--;

        if ($this->transactionDepth > 0) {
            $this->execute('ROLLBACK TO SAVEPOINT level' . $this->transactionDepth);

            return;
        }

        try {
            $this->pdo->rollBack() ?: throw TransactionException::occurAtRollback();
        } catch (PDOException $e) {
            throw TransactionException::occurAtRollback($e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isInTransaction(): bool
    {
        if (null === $this->pdo) {
            throw DisconnectedException::occur();
        }

        return $this->pdo->inTransaction();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function disconnect(): void
    {
        $this->transactionDepth = 0;
        $this->pdo = null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reconnect(): void
    {
        $this->transactionDepth = 0;
        $this->pdo = $this->factory->create();
    }

    /**
     * @param string $sql
     * @param array<string,mixed> $bindParams
     * @return PDOStatement
     * @throws DisconnectedException
     * @throws StorageException
     */
    private function prepareStatement(string $sql, array $bindParams = []): PDOStatement
    {
        if (null === $this->pdo) {
            throw DisconnectedException::occur();
        }

        try {
            return $this->pdo->prepare($sql)
                ?: throw StorageException::occur(
                    sprintf(
                        'Could not prepare statement for sql: %s --- with parameters: %s.',
                        $sql,
                        (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                    )
                );
        } catch (PDOException $e) {
            throw StorageException::occur(
                sprintf(
                    'Could not prepare statement for sql: %s --- with parameters: %s.',
                    $sql,
                    (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                ),
                $e
            );
        }
    }

    /**
     * @param PDOStatement $stmt
     * @param string $sql
     * @param array<string,mixed> $bindParams
     * @return void
     * @throws StorageException
     */
    private function executeStatement(
        PDOStatement $stmt,
        string $sql,
        array $bindParams = []
    ): void {
        try {
            /**
             * @var string $key
             * @var mixed $value
             */
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value, $this->getType($value));
            }
            $stmt->execute()
                ?: throw StorageException::occur(
                sprintf(
                    'Could not execute sql statement: %s --- with parameters: %s.',
                    $sql,
                    (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                )
            );
        } catch (PDOException $e) {
            throw StorageException::occur(
                sprintf(
                    'Could not execute sql statement: %s --- with parameters: %s.',
                    $sql,
                    (false === $paramsLog = json_encode($bindParams)) ? '' : $paramsLog
                ),
                $e
            );
        }
    }

    /**
     * @param mixed $value
     * @return int
     */
    private function getType(mixed $value): int
    {
        return match (gettype($value)) {
            'integer' => PDO::PARAM_INT,
            'boolean' => PDO::PARAM_BOOL,
            'NULL' => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
    }
}