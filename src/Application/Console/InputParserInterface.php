<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

interface InputParserInterface
{
    /**
     * @param array<int,string> $argv
     * @param array<int,string> $argNames
     * @return InputInterface
     */
    public function parse(array $argv, array $argNames = []): InputInterface;
}