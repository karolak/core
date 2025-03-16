<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use Karolak\Core\Application\Console\CommandArgument;
use Karolak\Core\Application\Console\CommandDescription;
use Karolak\Core\Application\Console\CommandInterface;
use Karolak\Core\Application\Console\InputInterface;
use Karolak\Core\Application\Console\OutputInterface;
use Karolak\Core\Application\Console\Status;
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