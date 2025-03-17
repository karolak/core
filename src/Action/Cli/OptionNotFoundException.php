<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

use Exception;

final class OptionNotFoundException extends Exception
{
    /**
     * @param string $name
     * @return self
     */
    public static function forName(string $name): self
    {
        return new self(sprintf('Input option not found for name %s.', $name));
    }
}