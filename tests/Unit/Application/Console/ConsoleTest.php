<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Console;

use Karolak\Core\Application\Console\CommandInterface;
use Karolak\Core\Application\Console\Console;
use Karolak\Core\Application\Console\ConsoleConfigInterface;
use Karolak\Core\Application\Console\Status;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

#[
    UsesClass(Console::class),
    CoversClass(Console::class)
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
        $command = $this->createMock(CommandInterface::class);
        $command
            ->expects($this->once())
            ->method('run')
            ->willReturn(Status::SUCCESS);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                function (string $key) use ($command){
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => $command::class]),
                        $command::class => $command,
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
        $command = $this->createMock(CommandInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->getConfig(['test' => $command::class]));

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
        $command = $this->createMock(CommandInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(
                function (string $key) use ($command){
                    return match ($key) {
                        ConsoleConfigInterface::class => $this->getConfig(['test' => $command::class]),
                        $command::class => throw new \Exception('Object not found in container.'),
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