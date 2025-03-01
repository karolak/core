<?php

declare(strict_types=1);

namespace Karolak\Core\Application\View;

interface ViewConfigInterface
{
    /**
     * @return string Templates directory location.
     */
    public function getTemplatesDirectory(): string;
}