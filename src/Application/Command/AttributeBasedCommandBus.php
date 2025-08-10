<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Command;

use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

final readonly class AttributeBasedCommandBus implements CommandBusInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function dispatch(CommandInterface $command): void
    {
        try {
            $attribute = new ReflectionClass($command::class)
                ->getAttributes(CommandFor::class)[0]
                ?? throw CommandHandlerNotFoundException::for($command::class);

            /** @var object $commandHandler */
            $commandHandler = $this->container->get($attribute->newInstance()->commandHandlerClass);
            $commandHandler instanceof CommandHandlerInterface
                ? $commandHandler->handle($command)
                : throw InvalidCommandHandlerException::with($command::class, $commandHandler::class);
        } catch (ReflectionException | ContainerExceptionInterface $e) {
            throw CommandHandlerNotFoundException::for($command::class, $e);
        }
    }
}