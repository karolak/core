<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use Stringable;

interface IdInterface extends Stringable
{
    /**
     * @param string|Stringable $id
     * @return IdInterface
     */
    public static function fromString(string|Stringable $id): IdInterface;
}