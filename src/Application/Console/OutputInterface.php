<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface OutputInterface
{
    /**
     * @param string $message
     * @return void
     */
    public function write(string $message): void;

    /**
     * @param string $message
     * @return void
     */
    public function writeln(string $message): void;
}