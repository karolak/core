<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface ConsoleConfigInterface
{
    /**
     * @return array<string,class-string>
     */
    public function getCommands(): array;
}