<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Infrastructure\Logger;

use Karolak\Core\Application\Logger\Config\LoggerConfigInterface;
use Karolak\Core\Infrastructure\Logger\FileHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(FileHandler::class),
    CoversClass(FileHandler::class)
]
final class FileHandlerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldCreateLogDirWhenDoesNotExistsAndWriteToFile(): void
    {
        // given
        $config = $this->createMock(LoggerConfigInterface::class);
        $dirName = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'new_directory_' . time();
        $config
            ->method('getFileLogDirPath')
            ->willReturn($dirName);
        $config
            ->method('getRecordTemplate')
            ->willReturn('[%timestamp%] [%level%]: %message%');
        $handler = new FileHandler($config);
        $logFile = $dirName . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';

        // when
        $handler->handle([
            'level' => 'ERROR',
            'message' => 'Error message.',
            'timestamp' => '2025-01-01 00:00:00',
        ]);

        // then
        $this->assertFileExists($logFile);
        $this->assertFileIsWritable($logFile);
        $this->assertStringEqualsFile($logFile, "[2025-01-01 00:00:00] [ERROR]: Error message.\n");
    }
}