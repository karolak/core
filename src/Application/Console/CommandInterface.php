<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface CommandInterface
{
    /**
     * @return Status
     */
    public function run(): Status;
}