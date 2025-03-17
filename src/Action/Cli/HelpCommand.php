<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

use Override;
use ReflectionClass;
use ReflectionException;

#[CommandDescription('Help command.')]
final readonly class HelpCommand implements CommandInterface
{
    public function __construct(
        private ConsoleConfigInterface $consoleConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function run(InputInterface $input, OutputInterface $output): Status
    {
        $output->writeln('Available commands:');

        foreach ($this->consoleConfig->getCommands() as $command => $commandClass) {
            $output->writeln('* "' . $command . '" - ' . $this->getCommandDescription($commandClass, 'NO DESCRIPTION'));
        }

        return Status::SUCCESS;
    }

    /**
     * @param class-string $commandClass
     * @param string $defaultDescription
     * @return string
     */
    private function getCommandDescription(string $commandClass, string $defaultDescription): string
    {
        try {
            $description = $defaultDescription;
            $attributes = new ReflectionClass($commandClass)->getAttributes(CommandDescription::class);
            foreach ($attributes as $attribute) {
                $description = $attribute->newInstance()->getDescription();
            }

            return $description;
        } catch (ReflectionException) {
            return $defaultDescription;
        }
    }
}