<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

interface InputParserInterface
{
    /**
     * @param array<int,string> $argv
     * @param array<int,string> $argNames
     * @return InputInterface
     */
    public function parse(array $argv, array $argNames = []): InputInterface;
}