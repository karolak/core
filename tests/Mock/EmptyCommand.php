<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use Karolak\Core\Action\Cli\CommandArgument;
use Karolak\Core\Action\Cli\CommandDescription;
use Karolak\Core\Action\Cli\CommandInterface;
use Karolak\Core\Action\Cli\InputInterface;
use Karolak\Core\Action\Cli\OutputInterface;
use Karolak\Core\Action\Cli\Status;
use Override;

#[
    CommandDescription('Empty test command.'),
    CommandArgument('arg1'),
    CommandArgument('arg2')
]
readonly class EmptyCommand implements CommandInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function run(InputInterface $input, OutputInterface $output): Status
    {
        return Status::SUCCESS;
    }
}