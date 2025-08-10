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
}