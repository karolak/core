<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class CommandArgument
{
    /**
     * @param string $name
     */
    public function __construct(
        private string $name
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}