<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Console;

use Karolak\Core\Application\Console\CommandDescription;
use Karolak\Core\Application\Console\CommandInterface;
use Karolak\Core\Application\Console\ConsoleConfigInterface;
use Karolak\Core\Application\Console\HelpCommand;
use Karolak\Core\Application\Console\InputInterface;
use Karolak\Core\Application\Console\OutputInterface;
use Karolak\Core\Application\Console\Status;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(HelpCommand::class),
    UsesClass(CommandDescription::class),
    CoversClass(HelpCommand::class),
    CoversClass(CommandDescription::class),
]
final class HelpCommandTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldReturnSuccess(): void
    {
        // given
        $emptyCommand = $this->getEmptyCommandWithoutAttributes();
        $config = $this->createMock(ConsoleConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getCommands')
            ->willReturn([
                'help' => HelpCommand::class,
                'empty' => $emptyCommand::class,
                'not-exists' => 'NOT_EXISTS'
            ]);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $helpCommand = new HelpCommand($config);

        // when
        $result = $helpCommand->run($input, $output);

        // then
        $this->assertEquals(Status::SUCCESS, $result);
    }

    /**
     * @return CommandInterface
     */
    private function getEmptyCommandWithoutAttributes(): CommandInterface
    {
        return new readonly class() implements CommandInterface {
            /**
             * @inheritDoc
             */
            #[Override]
            public function run(InputInterface $input, OutputInterface $output): Status
            {
                return Status::SUCCESS;
            }
        };
    }
}