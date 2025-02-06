<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

class EmptyObject implements EmptyInterface
{
    /**
     * @inheritDoc
     */
    public function getValue(): int
    {
        return 0;
    }
}