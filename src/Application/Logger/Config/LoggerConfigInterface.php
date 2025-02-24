<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger\Config;

interface LoggerConfigInterface
{
    /**
     * @return string
     */
    public function getFileLogDirPath(): string;

    /**
     * @return string|null
     */
    public function getRecordTemplate(): ?string;

    /**
     * @return string|null
     */
    public function getRecordDateTimeFormat(): ?string;
}