<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface ConsoleInterface
{
    /**
     * @param array<int,string> $args
     * @return Status
     */
    public function run(array $args): Status;
}