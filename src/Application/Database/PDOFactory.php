<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

use Override;
use PDO;
use PDOException;

final readonly class PDOFactory implements PDOFactoryInterface
{
    /**
     * @param DatabaseConfigInterface $config
     */
    public function __construct(private DatabaseConfigInterface $config)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function create(): PDO
    {
        try {
            $pdo = new PDO($this->config->getConnectionString());
            $options = $this->config->getDriverOptions();
            /**
             * @var int|string $option
             * @var mixed $value
             */
            foreach ($options as $option => $value) {
                if (false === is_int($option)) {
                    continue;
                }
                $pdo->setAttribute($option, $value);
            }

            return $pdo;
        } catch (PDOException $e) {
            throw StorageException::occur($e->getMessage(), $e);
        }
    }
}