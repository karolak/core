<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

readonly class DummyListener
{
    public function __invoke(DummyEvent $event): void
    {
    }
}