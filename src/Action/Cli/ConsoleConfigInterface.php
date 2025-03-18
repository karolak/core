<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

interface ConsoleConfigInterface
{
    /**
     * @return array<string,class-string>
     */
    public function getCommands(): array;
}