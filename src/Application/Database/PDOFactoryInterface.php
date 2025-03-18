<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

use PDO;

interface PDOFactoryInterface
{
    /**
     * @return PDO
     * @throws StorageException
     */
    public function create(): PDO;
}