<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

enum Status: int
{
    case SUCCESS = 0;
    case FAILURE = 1;
    case EXCEPTION = 2;
}