<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Action\Cli;

use Karolak\Core\Action\Cli\ArgumentNotFoundException;
use Karolak\Core\Action\Cli\InputParser;
use Karolak\Core\Action\Cli\OptionNotFoundException;
use Karolak\Core\Action\Cli\StandardInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(InputParser::class),
    UsesClass(StandardInput::class),
    CoversClass(InputParser::class)
]
final class InputParserTest extends TestCase
{
    /**
     * @return void
     * @throws ArgumentNotFoundException
     */
    public function testShouldParseAllArguments(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'val1', 'val2', 'val3'];
        $argNames = ['arg1', 'arg2', 'arg3'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertTrue($input->hasArgument('arg2'));
        $this->assertTrue($input->hasArgument('arg3'));

        $this->assertEquals('val1', $input->getArgument('arg1'));
        $this->assertEquals('val2', $input->getArgument('arg2'));
        $this->assertEquals('val3', $input->getArgument('arg3'));

        $this->assertCount(3, $input->getArguments());
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     */
    public function testShouldParseOnlyNamedArgumentsWhenGivenMoreOnInput(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'val1', 'val2', 'val3'];
        $argNames = ['arg1', 'arg2'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertTrue($input->hasArgument('arg2'));
        $this->assertFalse($input->hasArgument('arg3'));

        $this->assertEquals('val1', $input->getArgument('arg1'));
        $this->assertEquals('val2', $input->getArgument('arg2'));

        $this->assertCount(2, $input->getArguments());
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     */
    public function testShouldParseOnlyNamedArgumentsWhenGivenLessOnInput(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'val1', 'val2'];
        $argNames = ['arg1', 'arg2', 'arg3'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertTrue($input->hasArgument('arg2'));
        $this->assertFalse($input->hasArgument('arg3'));

        $this->assertEquals('val1', $input->getArgument('arg1'));
        $this->assertEquals('val2', $input->getArgument('arg2'));

        $this->assertCount(2, $input->getArguments());
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseShortOptionsWithNoValues(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a', '-b'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertTrue($input->hasOption('b'));

        $this->assertIsBool($input->getOption('a'));
        $this->assertIsBool($input->getOption('b'));

        $this->assertTrue($input->getOption('a'));
        $this->assertTrue($input->getOption('b'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseShortOptionsWithValues(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a=test_1', '-b=test 2'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertTrue($input->hasOption('b'));

        $this->assertIsString($input->getOption('a'));
        $this->assertIsString($input->getOption('b'));

        $this->assertEquals('test_1', $input->getOption('a'));
        $this->assertEquals('test 2', $input->getOption('b'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseLongOptionsWithNoValues(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '--option1', '--option2'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertTrue($input->hasOption('option2'));

        $this->assertIsBool($input->getOption('option1'));
        $this->assertIsBool($input->getOption('option2'));

        $this->assertTrue($input->getOption('option1'));
        $this->assertTrue($input->getOption('option2'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseLongOptionsWithValues(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '--option1=test_1', '--option2=test 2'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertTrue($input->hasOption('option2'));

        $this->assertIsString($input->getOption('option1'));
        $this->assertIsString($input->getOption('option2'));

        $this->assertEquals('test_1', $input->getOption('option1'));
        $this->assertEquals('test 2', $input->getOption('option2'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenShortOptionWithoutValueIsBeforeArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a', 'argument-value1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertIsBool($input->getOption('a'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenShortOptionWithValueIsBeforeArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a=test', 'argument-value1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertIsString($input->getOption('a'));
        $this->assertEquals('test', $input->getOption('a'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenShortOptionWithoutValueIsAfterArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'argument-value1', '-a'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertIsBool($input->getOption('a'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenShortOptionWithValueIsAfterArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'argument-value1', '-a=test'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('a'));
        $this->assertIsString($input->getOption('a'));
        $this->assertEquals('test', $input->getOption('a'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenLongOptionWithoutValueIsBeforeArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '--option1', 'argument-value1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertIsBool($input->getOption('option1'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenLongOptionWithValueIsBeforeArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '--option1=option-value1', 'argument-value1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertIsString($input->getOption('option1'));
        $this->assertEquals('option-value1', $input->getOption('option1'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenLongOptionWithoutValueIsAfterArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'argument-value1', '--option1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertIsBool($input->getOption('option1'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     * @throws OptionNotFoundException
     */
    public function testShouldParseWhenLongOptionWithValueIsAfterArgument(): void
    {
        // given
        $argv = ['bin/console', 'command-name', 'argument-value1', '--option1=option-value1'];
        $argNames = ['arg1'];

        // when
        $input = new InputParser()->parse($argv, $argNames);

        // then
        $this->assertTrue($input->hasOption('option1'));
        $this->assertIsString($input->getOption('option1'));
        $this->assertEquals('option-value1', $input->getOption('option1'));

        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertEquals('argument-value1', $input->getArgument('arg1'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseMultipleTimesSameShortOptionWithoutValue(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a', '-a'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('a'));

        $this->assertIsArray($input->getOption('a'));

        $this->assertEquals([true, true], $input->getOption('a'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseMultipleTimesSameShortOptionWithValue(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-a=1', '-a=2'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('a'));

        $this->assertIsArray($input->getOption('a'));

        $this->assertEquals(['1', '2'], $input->getOption('a'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseMultipleTimesSameLongOptionWithoutValue(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-option', '-option'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('option'));

        $this->assertIsArray($input->getOption('option'));

        $this->assertEquals([true, true], $input->getOption('option'));
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldParseMultipleTimesSameLongOptionWithValue(): void
    {
        // given
        $argv = ['bin/console', 'command-name', '-option=1', '-option=2'];

        // when
        $input = new InputParser()->parse($argv);

        // then
        $this->assertTrue($input->hasOption('option'));

        $this->assertIsArray($input->getOption('option'));

        $this->assertEquals(['1', '2'], $input->getOption('option'));
    }
}