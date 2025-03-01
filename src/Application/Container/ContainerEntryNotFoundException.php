<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

final class ContainerEntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    /**
     * @param string $id
     * @return self
     */
    public static function forService(string $id): self
    {
        return new self(sprintf('Container entry not found for service %s.', $id));
    }
}
