<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use Exception;

final class NoEventsProvidedException extends Exception
{
    /**
     * @return self
     */
    public static function occur(): self
    {
        return new self('No events provided.');
    }
}