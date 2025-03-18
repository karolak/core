<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

use Exception;

final class DisconnectedException extends Exception
{
    /**
     * @return self
     */
    public static function occur(): self
    {
        return new self('Disconnected occurred.');
    }
}