<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use DateTimeImmutable;
use Karolak\Core\Domain\EventInterface;
use Override;

readonly class DummyEvent implements EventInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}