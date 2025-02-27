<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger;

interface HandlerInterface
{
    /**
     * @param array<string,null|bool|string|int|float|double> $data
     * @return void
     */
    public function handle(array $data): void;
}