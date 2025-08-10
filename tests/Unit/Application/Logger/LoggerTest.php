<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Logger;

use Karolak\Core\Application\Logger\DefaultConfigTrait;
use Karolak\Core\Application\Logger\HandlerInterface;
use Karolak\Core\Application\Logger\Logger;
use Karolak\Core\Application\Logger\LoggerConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[
    CoversClass(Logger::class),
    CoversTrait(DefaultConfigTrait::class)
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
        $config = new readonly class implements LoggerConfigInterface {
            use DefaultConfigTrait;
        };
        $handler = $this->createMock(HandlerInterface::class);
        $logger = new Logger($config, $handler);

        // then
        $handler
            ->expects($this->once())
            ->method('handle');

        // when
        $logger->log(LogLevel::ERROR, 'Error message {bar}.', ['bar' => 'foo']);
    }
}