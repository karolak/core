<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

interface DummyInterface
{
    /**
     * @return int
     */
    public function getValue(): int;
}