<?php

declare(strict_types=1);

namespace Karolak\Core\Action\Cli;

interface InputInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool;

    /**
     * @param string $name
     * @return string
     * @throws ArgumentNotFoundException
     */
    public function getArgument(string $name): string;

    /**
     * @param string $name
     * @return bool|string|array<int,bool|string>
     * @throws OptionNotFoundException
     */
    public function getOption(string $name): bool|string|array;

    /**
     * @return array<string,string>
     */
    public function getArguments(): array;

    /**
     * @return array<string,bool|string|array<int,bool|string>>
     */
    public function getOptions(): array;
}