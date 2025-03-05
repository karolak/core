<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Database;

interface DatabaseConfigInterface
{
    /**
     * @return string
     */
    public function getConnectionString(): string;

    /**
     * @return array<int|string,mixed>
     */
    public function getDriverOptions(): array;
}