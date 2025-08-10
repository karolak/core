<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

interface CommandHandlerInterface
{
    /**
     * @param CommandInterface $command
     * @return void
     */
    public function handle(CommandInterface $command): void;
}