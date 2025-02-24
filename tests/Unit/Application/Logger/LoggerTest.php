<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Logger;

use Karolak\Core\Application\Logger\Config\LoggerConfigInterface;
use Karolak\Core\Application\Logger\HandlerInterface;
use Karolak\Core\Application\Logger\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[
    UsesClass(Logger::class),
    CoversClass(Logger::class)
]
final class LoggerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldLog(): void
    {
        // given
        $config = $this->createMock(LoggerConfigInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $logger = new Logger($config, $handler);

        // then
        $config
            ->expects($this->once())
            ->method('getRecordDateTimeFormat')
            ->willReturn('Y-m-d H:i:s');
        $handler
            ->expects($this->once())
            ->method('handle');

        // when
        $logger->log(LogLevel::ERROR, 'Error message', ['foo' => 'bar']);
    }
}