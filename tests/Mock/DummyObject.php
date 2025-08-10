<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use Override;

readonly class DummyObject implements DummyInterface
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