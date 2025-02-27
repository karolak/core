<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger;

use DateTimeImmutable;
use Karolak\Core\Application\Logger\Config\LoggerConfigInterface;
use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

final class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * @param LoggerConfigInterface $config
     * @param HandlerInterface $handler
     */
    public function __construct(
        private readonly LoggerConfigInterface $config,
        private readonly HandlerInterface $handler
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->handler->handle([
            'timestamp' => new DateTimeImmutable()->format($this->config->getDateTimeFormat()),
            'level' => is_string($level) ? $level : '',
            'message' => $this->interpolate(strval($message), $context)
        ]);
    }

    /**
     * @param string $message
     * @param array<array-key,mixed> $context
     * @return string
     */
    private function interpolate(string $message, array $context = []): string
    {
        $replace = [];
        /**
         * @var string $key
         * @var null|bool|string|int|float|Stringable $value
         */
        foreach ($context as $key => $value) {
            if (is_string($value) || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replace);
    }
}