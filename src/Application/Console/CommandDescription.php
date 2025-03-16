<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CommandDescription
{
    public function __construct(
        private string $description
    ) {
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}