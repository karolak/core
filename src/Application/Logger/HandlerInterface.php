<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger;

interface HandlerInterface
{
    /**
     * @param array<string,null|bool|int|float|string> $data
     * @return void
     */
    public function handle(array $data): void;
}