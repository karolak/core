<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

use Exception;

final class InvalidQueryHandlerException extends Exception
{
    /**
     * @param string $queryClass
     * @param string $queryHandlerClass
     * @return static
     */
    public static function with(string $queryClass, string $queryHandlerClass): self
    {
        return new self(
            sprintf(
                'Invalid handler %s for query %s.',
                $queryHandlerClass,
                $queryClass
            )
        );
    }
}