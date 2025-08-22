<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use DateTimeImmutable;
use Karolak\Core\Domain\EventInterface;
use Override;

class DummyEvent implements EventInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function toPayload(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function fromPayload(array $payload): self
    {
        return new self();
    }
}