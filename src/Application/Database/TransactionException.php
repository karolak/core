<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

use Exception;
use Throwable;

final class TransactionException extends Exception
{
    /**
     * @param Throwable|null $previous
     * @return static
     */
    public static function occurAtBegin(?Throwable $previous = null): self
    {
        return new self(
            'Transaction already started or the driver does not support transactions.',
            0,
            $previous
        );
    }

    /**
     * @param Throwable|null $previous
     * @return static
     */
    public static function occurAtRollback(?Throwable $previous = null): self
    {
        return new self(
            'Transaction never started or rollback failed.',
            0,
            $previous
        );
    }

    /**
     * @param Throwable|null $previous
     * @return static
     */
    public static function occurAtCommit(?Throwable $previous = null): self
    {
        return new self(
            'Transaction never started or commit failed.',
            0,
            $previous
        );
    }
}