<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use DateTimeImmutable;

interface EventInterface
{
    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * @return array<mixed>
     */
    public function toPayload(): array;

    /**
     * @param array<mixed> $payload
     * @return self
     */
    public static function fromPayload(array $payload): self;
}