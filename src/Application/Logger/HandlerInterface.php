<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger;

interface HandlerInterface
{
    public const string DEFAULT_RECORD_FORMAT = '[%timestamp%] [%level%]: %message%';

    /**
     * @param array<string,null|bool|string|int|float|double> $record
     * @return void
     */
    public function handle(array $record): void;
}