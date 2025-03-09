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
    public function testRunCommandWithSuccess(): void
    {
        // given
        $commandLine = 'bin/console test';
        $command = $this->createMock(CommandInterface::class);
        $config = $this->getConfig(['test' => $command::class]);
        $command
            ->expects($this->once())
            ->method('run')
            ->willReturn(Status::SUCCESS);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with($command::class)
            ->willReturn($command);
        $console = new Console($config, $container);

        // when
        $result = $console->run(explode(' ', $commandLine));

        // then
        $this->assertEquals(Status::SUCCESS, $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnExceptionStatusWhenCommandNotFound(): void
    {
        // given
        $commandLine = 'bin/console not_found';
        $config = $this->getConfig(['test' => CommandInterface::class]);
        $container = $this->createMock(ContainerInterface::class);
        $console = new Console($config, $container);

        // when
        $this->expectOutputRegex('/not_found/');
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
        // given
        $commandLine = 'bin/console test';
        $command = new stdClass();
        $config = $this->getConfig(['test' => $command::class]);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with($command::class)
            ->willReturn($command);

        $console = new Console($config, $container);

        // when
        $this->expectOutputRegex('/CommandInterface/');
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