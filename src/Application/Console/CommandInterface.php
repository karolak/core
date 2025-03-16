<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface CommandInterface
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Status
     */
    public function run(InputInterface $input, OutputInterface $output): Status;
}