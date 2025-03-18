<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

use Exception;
use Throwable;

final class StorageException extends Exception
{
    /**
     * @param string $message
     * @param Throwable|null $previous
     * @return self
     */
    public static function occur(string $message, ?Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }
}