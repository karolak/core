<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

use Exception;
use Throwable;

final class CommandHandlerNotFoundException extends Exception
{
    /**
     * @param string $commandClass
     * @param Throwable|null $previous
     * @return static
     */
    public static function for(string $commandClass, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('CommandHandler not found for command %s.', $commandClass),
            0,
            $previous
        );
    }
}