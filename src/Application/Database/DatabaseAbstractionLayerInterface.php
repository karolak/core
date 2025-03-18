<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

interface DatabaseAbstractionLayerInterface
{
    /**
     * @param string $sql
     * @param array<string,mixed> $bindParams
     * @throws DisconnectedException
     * @throws StorageException
     */
    public function execute(string $sql, array $bindParams = []): void;

    /**
     * @param string $sql
     * @param array<string,mixed> $bindParams
     * @return array<string,mixed>
     * @throws DisconnectedException
     * @throws StorageException
     */
    public function fetch(string $sql, array $bindParams = []): array;

    /**
     * @param string $sql
     * @param array<string,mixed> $bindParams
     * @return array<int,array<string,mixed>>
     * @throws DisconnectedException
     * @throws StorageException
     */
    public function fetchAll(string $sql, array $bindParams = []): array;

    /**
     * @return void
     * @throws DisconnectedException
     * @throws TransactionException
     * @throws StorageException
     */
    public function beginTransaction(): void;

    /**
     * @return void
     * @throws DisconnectedException
     * @throws TransactionException
     * @throws StorageException
     */
    public function commitTransaction(): void;

    /**
     * @return void
     * @throws DisconnectedException
     * @throws TransactionException
     * @throws StorageException
     */
    public function rollbackTransaction(): void;

    /**
     * @return bool
     * @throws DisconnectedException
     */
    public function isInTransaction(): bool;

    /**
     * @return void
     */
    public function disconnect(): void;

    /**
     * @return void
     * @throws StorageException
     */
    public function reconnect(): void;
}