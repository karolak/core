<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Logger\Config;

interface LoggerConfigInterface
{
    /**
     * @return string Logs channel.
     */
    public function getChannel(): string;

    /**
     * @return string Path to directory where you want to hold logs files.
     */
    public function getLogsDirectory(): string;

    /**
     * @return string Format for single log line.
     */
    public function getLineFormat(): string;

    /**
     * @return string DateTime compatible format in log file.
     */
    public function getDateTimeFormat(): string;
}