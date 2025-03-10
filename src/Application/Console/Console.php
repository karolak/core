<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Exception;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

final readonly class Console implements ConsoleInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param array<int,string> $argv
     * @return Status
     */
    #[Override]
    public function run(array $argv): Status
    {
        try {
            $config = $this->getConfig();
            $commandName = $argv[1] ?? '';
            $commandClass = $config->getCommands()[$commandName] ?? null;
            if (null === $commandClass) {
                throw new Exception(sprintf('Command "%s" not found in container.', $commandName));
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

    /**
     * @return ConsoleConfigInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getConfig(): ConsoleConfigInterface
    {
        $config = $this->container->get(ConsoleConfigInterface::class);
        if (false === ($config instanceof ConsoleConfigInterface)) {
            throw new Exception(sprintf('Container key "%s" should implement ConsoleConfigInterface.', ConsoleConfigInterface::class));
        }

        return $config;
    }
}