<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Exception;
use Override;
use Psr\Container\ContainerInterface;
use Throwable;

final readonly class Console implements ConsoleInterface
{
    /**
     * @param ConsoleConfigInterface $config
     * @param ContainerInterface $container
     */
    public function __construct(
        private ConsoleConfigInterface $config,
        private ContainerInterface $container
    ) {
    }

    /**
     * @param array<int,string> $args
     * @return Status
     */
    #[Override]
    public function run(array $args): Status
    {
        try {
            $commandName = $args[1] ?? '';
            $commandClass = $this->config->getCommands()[$commandName] ?? null;
            if (null === $commandClass) {
                throw new Exception(sprintf('Command "%s" not found.', $commandName));
            }
            $command = $this->container->get($commandClass);
            if (false === ($command instanceof CommandInterface)) {
                throw new Exception(sprintf('Command class "%s" should implement CommandInterface.', $commandClass));
            }

            return $command->run();
        } catch (Throwable $e) {
            echo $e->getMessage();

            return Status::EXCEPTION;
        }
    }
}