<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Override;

final readonly class StandardInput implements InputInterface
{
    /**
     * @param array<string,string> $arguments
     * @param array<string,bool|string|array<int,bool|string>> $options
     */
    public function __construct(
        private array $arguments = [],
        private array $options = []
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getArgument(string $name): string
    {
        if (false === $this->hasArgument($name)) {
            throw ArgumentNotFoundException::forName($name);
        }

        return $this->arguments[$name];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getOption(string $name): bool|string|array
    {
        if (false === $this->hasOption($name)) {
            throw OptionNotFoundException::forName($name);
        }

        return $this->options[$name];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getOptions(): array
    {
        return $this->options;
    }
}