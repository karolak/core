<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Console;

use Karolak\Core\Application\Console\CommandArgument;
use Karolak\Core\Application\Console\Console;
use Karolak\Core\Application\Console\ConsoleConfigInterface;
use Karolak\Core\Application\Console\InputInterface;
use Karolak\Core\Application\Console\InputParserInterface;
use Karolak\Core\Application\Console\OutputInterface;
use Karolak\Core\Application\Console\Status;
use Karolak\Core\Tests\Mock\EmptyCommand;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

#[
    UsesClass(Console::class),
    CoversClass(Console::class),
    CoversClass(CommandArgument::class)
]
final class ConsoleTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRunCommandWithSuccess(): void
    {
        $commandLine = 'bin/console test';

        // given
        $inputParser = $this->createMock(InputParserInterface::class);
        $inputParser
            ->expects($this->once())
            ->method('parse')
            ->willReturn($this->createMock(InputInterface::class));

        $output = $this->createMock(OutputInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(
                function (string $key) use ($inputParser, $output) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => new EmptyCommand(),
                        InputParserInterface::class => $inputParser,
                        OutputInterface::class => $output,
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::SUCCESS, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenConfigNotFound(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputString('Object not found in container.');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('Object not found in container.'));

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenConfigNotImplementConsoleConfigInterface(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputRegex('/ConsoleConfigInterface/');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->willReturn(new stdClass());

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenCommandNotFound(): void
    {
        $commandLine = 'bin/console not_found';
        $this->expectOutputRegex('/not_found/');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->getConfig());

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenCommandNotFoundInContainer(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputString('Object not found in container.');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                function (string $key) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => throw new \Exception('Object not found in container.'),
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenCommandNotImplementCommandInterface(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputRegex('/CommandInterface/');

        // given
        $command = new stdClass();

        $config = $this->getConfig(['test' => $command::class]);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($config, $command);

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenInputParserNotFoundInContainer(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputString('Object not found in container.');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(
                function (string $key) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => new EmptyCommand(),
                        InputParserInterface::class => throw new \Exception('Object not found in container.'),
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenInputParserNotImplementInputParserInterface(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputRegex('/InputParserInterface/');

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(
                function (string $key) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => new EmptyCommand(),
                        InputParserInterface::class => new stdClass(),
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenOutputNotFoundInContainer(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputString('Object not found in container.');

        // given
        $inputParser = $this->createMock(InputParserInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(
                function (string $key) use ($inputParser) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => new EmptyCommand(),
                        InputParserInterface::class => $inputParser,
                        OutputInterface::class => throw new \Exception('Object not found in container.'),
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenOutputNotImplementOutputInterface(): void
    {
        $commandLine = 'bin/console test';
        $this->expectOutputRegex('/OutputInterface/');

        // given
        $inputParser = $this->createMock(InputParserInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(
                function (string $key) use ($inputParser) {
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => EmptyCommand::class]),
                        EmptyCommand::class => new EmptyCommand(),
                        InputParserInterface::class => $inputParser,
                        OutputInterface::class => new stdClass(),
                        default => null
                    };
                }
            );

        $console = new Console($container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::EXCEPTION, $result);
    }

    /**
     * @param array<string,class-string> $commands
     * @return ConsoleConfigInterface
     */
    private function getConfig(array $commands = []): ConsoleConfigInterface
    {
        return new readonly class($commands) implements ConsoleConfigInterface {
            /**
             * @param array<string,class-string> $commands
             */
            public function __construct(private array $commands) {}

            /**
             * @inheritDoc
             */
            #[Override]
            public function getCommands(): array
            {
                return $this->commands;
            }
        };
    }
}