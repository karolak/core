<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use Exception;

final class MissingMethodException extends Exception
{
    /**
     * @param string $method
     * @return self
     */
    public static function occurFor(string $method): self
    {
        return new self(sprintf('Method "%s" not found.', $method));
    }
}