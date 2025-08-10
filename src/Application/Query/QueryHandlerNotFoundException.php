<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

use Exception;
use Throwable;

final class QueryHandlerNotFoundException extends Exception
{
    /**
     * @param string $queryClass
     * @param Throwable|null $previous
     * @return static
     */
    public static function for(string $queryClass, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('QueryHandler not found for query %s.', $queryClass),
            0,
            $previous
        );
    }
}