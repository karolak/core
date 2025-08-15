<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ListenerFor
{
    /**
     * @param class-string $eventClass
     */
    public function __construct(private(set) string $eventClass)
    {
    }
}