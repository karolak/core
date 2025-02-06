<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    /**
     * @param Throwable|null $previous
     * @return self
     */
    public static function forInitializationWith(?Throwable $previous = null): self
    {
        return new self('Container could not be created.', 0, $previous);
    }
}
