<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class QueryFor
{
    /**
     * @param class-string $queryHandlerClass
     */
    public function __construct(private(set) string $queryHandlerClass)
    {
    }
}