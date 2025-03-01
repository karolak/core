<?php

declare(strict_types=1);

namespace Karolak\Core\Application\View;

use RuntimeException;

interface ViewInterface
{
    /**
     * @param string $template
     * @param array<string,null|bool|int|float|string|object> $parameters
     * @return string
     * @throws RuntimeException
     */
    public function render(string $template, array $parameters = []): string;
}