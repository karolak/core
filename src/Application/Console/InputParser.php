<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Console;

use Override;

final readonly class InputParser implements InputParserInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function parse(array $argv, array $argNames = []): InputInterface
    {
        $arguments = [];
        $options = [];
        array_shift($argv);
        array_shift($argv);

        foreach ($argv as $arg) {
            if ($this->isShortOption($arg)) {
                $options[$this->getShortOptionName($arg)][] = $this->getOptionValue($arg);
                continue;
            }

            if ($this->isLongOption($arg)) {
                $options[$this->getLongOptionName($arg)][] = $this->getOptionValue($arg);
                continue;
            }

            $arguments[] = $arg;
        }

        $combineCount = min(count($argNames), count($arguments));

        return new Input(
            array_combine(
                array_slice($argNames, 0, $combineCount),
                array_slice($arguments, 0, $combineCount)
            ),
            array_map(
                function (array $items) {
                    if (count($items) > 1) {
                        return $items;
                    }

                    return $items[0];
                },
                $options
            )
        );
    }

    /**
     * @param string $arg
     * @return bool
     */
    private function isShortOption(string $arg): bool
    {
        return strlen($arg) > 1 && $arg[0] === '-' && $arg[1] != '-';
    }

    /**
     * @param string $arg
     * @return bool
     */
    private function isLongOption(string $arg): bool
    {
        return strlen($arg) > 2 && str_starts_with($arg, '--');
    }

    /**
     * @param string $arg
     * @return string
     */
    private function getShortOptionName(string $arg): string
    {
        $parts = explode('=', $arg, 2);

        return substr($parts[0], 1);
    }

    /**
     * @param string $arg
     * @return string
     */
    private function getLongOptionName(string $arg): string
    {
        $parts = explode('=', $arg, 2);

        return substr($parts[0], 2);
    }

    /**
     * @param string $arg
     * @return bool|string
     */
    private function getOptionValue(string $arg): bool|string
    {
        $parts = explode('=', $arg, 2);
        if (count($parts) === 2) {
            return $parts[1];
        }

        return true;
    }
}