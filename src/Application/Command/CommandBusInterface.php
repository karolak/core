<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

interface CommandBusInterface
{
    /**
     * @param CommandInterface $command
     * @return void
     * @throws CommandHandlerNotFoundException
     * @throws InvalidCommandHandlerException
     */
    public function dispatch(CommandInterface $command): void;
}