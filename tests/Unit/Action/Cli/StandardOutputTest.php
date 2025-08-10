<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Action\Cli;

use Karolak\Core\Action\Cli\StandardOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StandardOutput::class)]
final class StandardOutputTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldWriteToStdout(): void
    {
        // then
        $this->expectOutputString("test");

        // when
        new StandardOutput()->write('test');
    }

    /**
     * @return void
     */
    public function testShouldWriteWithNewLineToStdout(): void
    {
        // then
        $this->expectOutputString("test\n");

        // when
        new StandardOutput()->writeln('test');
    }
}