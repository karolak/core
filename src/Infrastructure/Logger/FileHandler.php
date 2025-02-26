<?php

declare(strict_types=1);

namespace Karolak\Core\Infrastructure\Logger;

use Karolak\Core\Application\Logger\Config\LoggerConfigInterface;
use Karolak\Core\Application\Logger\HandlerInterface;
use Override;

final readonly class FileHandler implements HandlerInterface
{
    /**
     * @param LoggerConfigInterface $config
     */
    public function __construct(private LoggerConfigInterface $config)
    {
        if (false === file_exists($this->config->getFileLogDirPath())) {
            mkdir($this->config->getFileLogDirPath(), 0777, true);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function handle(array $record): void
    {
        $output = $this->config->getRecordTemplate() ?? HandlerInterface::DEFAULT_RECORD_FORMAT;
        foreach ($record as $key => $val) {
            $output = str_replace('%' . $key . '%', strval($val), $output);
        }
        file_put_contents(
            $this->config->getFileLogDirPath() . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log',
            $output . PHP_EOL,
            FILE_APPEND
        );
    }
}