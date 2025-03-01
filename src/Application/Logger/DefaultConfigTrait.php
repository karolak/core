<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger;

trait DefaultConfigTrait
{
    /**
     * @return string
     */
    public function getChannel(): string
    {
        return 'app';
    }

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
        return '[%timestamp%] %channel%.%level%: %message%';
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string
    {
        return 'Y-m-d H:i:s';
    }
}