<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Override;

final class StandardOutput implements OutputInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function write(string $message): void
    {
        print $message;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function writeln(string $message): void
    {
        print $message . PHP_EOL;
    }
}