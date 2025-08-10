<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

use Exception;

final class InvalidCommandHandlerException extends Exception
{
    /**
     * @param string $commandClass
     * @param string $commandHandlerClass
     * @return static
     */
    public static function with(string $commandClass, string $commandHandlerClass): self
    {
        return new self(
            sprintf(
                'Invalid handler %s for command %s.',
                $commandHandlerClass,
                $commandClass
            )
        );
    }
}