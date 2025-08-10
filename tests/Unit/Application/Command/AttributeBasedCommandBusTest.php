<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Command;

use Karolak\Core\Application\Command\AttributeBasedCommandBus;
use Karolak\Core\Application\Command\CommandFor;
use Karolak\Core\Application\Command\CommandHandlerInterface;
use Karolak\Core\Application\Command\CommandHandlerNotFoundException;
use Karolak\Core\Application\Command\CommandInterface;
use Karolak\Core\Application\Command\InvalidCommandHandlerException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[
    CoversClass(AttributeBasedCommandBus::class),
    CoversClass(CommandFor::class),
    CoversClass(CommandHandlerNotFoundException::class),
    COversClass(InvalidCommandHandlerException::class)
]
final class AttributeBasedCommandBusTest extends TestCase
{
    /**
     * @return void
     * @throws CommandHandlerNotFoundException
     * @throws Exception
     * @throws InvalidCommandHandlerException
     */
    public function testShouldDispatchCommand(): void
    {
        // given
        $commandHandler = $this->createMock(CommandHandlerInterface::class);
        $commandHandler->expects($this->once())->method('handle');
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(CommandHandlerInterface::class)
            ->willReturn($commandHandler);

        $commandBus = new AttributeBasedCommandBus($container);

        // when
        $command = new #[CommandFor(CommandHandlerInterface::class)] class implements CommandInterface {};
        $commandBus->dispatch($command);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidCommandHandlerException
     */
    public function testShouldThrowExceptionWhenAttributeIsMissing(): void
    {
        // then
        $this->expectException(CommandHandlerNotFoundException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->never())
            ->method('get');

        $commandBus = new AttributeBasedCommandBus($container);

        // when
        $command = new class implements CommandInterface {};
        $commandBus->dispatch($command);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidCommandHandlerException
     */
    public function testShouldThrowExceptionWhenCommandHandlerNotFound(): void
    {
        // then
        $this->expectException(CommandHandlerNotFoundException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(CommandHandlerInterface::class)
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $commandBus = new AttributeBasedCommandBus($container);

        // when
        $command = new #[CommandFor(CommandHandlerInterface::class)] class implements CommandInterface {};
        $commandBus->dispatch($command);
    }

    /**
     * @return void
     * @throws CommandHandlerNotFoundException
     * @throws Exception
     * @throws InvalidCommandHandlerException
     */
    public function testShouldThrowExceptionWhenHandlerDoesNotImplementCommandHandlerInterface(): void
    {
        // then
        $this->expectException(InvalidCommandHandlerException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(CommandhandlerInterface::class)
            ->willReturn($this->createMock(CommandInterface::class));

        $commandBus = new AttributeBasedCommandBus($container);

        // when
        $command = new #[CommandFor(CommandHandlerInterface::class)] class implements CommandInterface {};
        $commandBus->dispatch($command);
    }
}