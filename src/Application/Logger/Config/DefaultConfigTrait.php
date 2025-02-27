<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger\Config;

trait DefaultConfigTrait
{
    /**
     * @return string
     */
    public function getLogsDirectory(): string
    {
        return DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * @return string
     */
    public function getLineFormat(): string
    {
        return '[%timestamp%] [%level%]: %message%';
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string
    {
        return 'c';
    }
}