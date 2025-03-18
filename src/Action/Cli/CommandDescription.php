<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CommandDescription
{
    /**
     * @param string $description
     */
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