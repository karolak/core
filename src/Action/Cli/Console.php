<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

use Exception;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use Throwable;

final readonly class Console implements ConsoleInterface
{
    private const int COMMAND_ARGV_INDEX = 1;

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
            $commandClass = $this->getCommandClass($argv[self::COMMAND_ARGV_INDEX] ?? '');

            $attributes = new ReflectionClass($commandClass)->getAttributes(CommandArgument::class);
            $argNames = [];
            foreach ($attributes as $attribute) {
                $argNames[] = $attribute->newInstance()->getName();
            }

            return $this->getCommand($commandClass)->run(
                $this->getInputParser()->parse($argv, $argNames),
                $this->getOutput()
            );
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

    /**
     * @param string $commandName
     * @return class-string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getCommandClass(string $commandName): string
    {
        $config = $this->getConfig();
        $commandClass = $config->getCommands()[$commandName] ?? null;
        if (null === $commandClass) {
            throw new Exception(sprintf('Command "%s" not found in container.', $commandName));
        }

        return $commandClass;
    }

    /**
     * @param string $commandClass
     * @return CommandInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getCommand(string $commandClass): CommandInterface
    {
        $command = $this->container->get($commandClass);
        if (false === ($command instanceof CommandInterface)) {
            throw new Exception(sprintf('Command class "%s" should implement CommandInterface.', $commandClass));
        }

        return $command;
    }

    /**
     * @return InputParserInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getInputParser(): InputParserInterface
    {
        $inputParser = $this->container->get(InputParserInterface::class);
        if (false === ($inputParser instanceof InputParserInterface)) {
            throw new Exception(sprintf('Input parser "%s" should implement InputParserInterface.', is_object($inputParser) ? get_class($inputParser) : gettype($inputParser)));
        }

        return $inputParser;
    }

    /**
     * @return OutputInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getOutput(): OutputInterface
    {
        $output = $this->container->get(OutputInterface::class);
        if (false === ($output instanceof OutputInterface)) {
            throw new Exception(sprintf('Output "%s" should implement OutputInterface.', is_object($output) ? get_class($output) : gettype($output)));
        }

        return $output;
    }
}