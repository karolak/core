<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CommandFor
{
    /**
     * @param class-string $commandHandlerClass
     */
    public function __construct(private(set) string $commandHandlerClass)
    {
    }
}