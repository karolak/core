<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Infrastructure\Logger;

use Karolak\Core\Application\Logger\DefaultConfigTrait;
use Karolak\Core\Application\Logger\LoggerConfigInterface;
use Karolak\Core\Infrastructure\Logger\FileHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(FileHandler::class),
    CoversTrait(DefaultConfigTrait::class)
]
final class FileHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldCreateLogDirWhenDoesNotExistsAndWriteToFile(): void
    {
        // given
        $config = new class implements LoggerConfigInterface {
            use DefaultConfigTrait;
        };
        $dirName = $config->getLogsDirectory();
        $handler = new FileHandler($config);
        $logFile = $dirName . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';

        // when
        $handler->handle([
            'timestamp' => '2025-01-01 00:00:00',
            'channel' => 'app',
            'level' => 'error',
            'message' => 'Error message.'
        ]);

        // then
        $this->assertFileExists($logFile);
        $this->assertFileIsWritable($logFile);
        $this->assertStringEqualsFile($logFile, "[2025-01-01 00:00:00] app.error: Error message.\n");
    }
}