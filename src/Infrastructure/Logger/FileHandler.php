<?php

declare(strict_types=1);

namespace Karolak\Core\Infrastructure\Logger;

use Karolak\Core\Application\Logger\HandlerInterface;
use Karolak\Core\Application\Logger\LoggerConfigInterface;
use Override;

final readonly class FileHandler implements HandlerInterface
{
    /**
     * @param LoggerConfigInterface $config
     */
    public function __construct(private LoggerConfigInterface $config)
    {
        if (false === file_exists($this->config->getLogsDirectory())) {
            mkdir($this->config->getLogsDirectory(), 0777, true);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function handle(array $data): void
    {
        $output = $this->config->getLineFormat();
        foreach ($data as $key => $val) {
            $output = str_replace('%' . $key . '%', strval($val), $output);
        }
        file_put_contents(
            $this->config->getLogsDirectory() . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log',
            $output . PHP_EOL,
            FILE_APPEND
        );
    }
}