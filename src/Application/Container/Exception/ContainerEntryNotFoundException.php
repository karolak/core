<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

final class ContainerEntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * @return self
     */
    public static function occured(): self
    {
        return new self('Container entry not found');
    }
}
