<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use Override;

readonly class EmptyObject implements EmptyInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): int
    {
        return 0;
    }
}