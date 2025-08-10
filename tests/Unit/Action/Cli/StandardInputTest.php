<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Action\Cli;

use Karolak\Core\Action\Cli\ArgumentNotFoundException;
use Karolak\Core\Action\Cli\OptionNotFoundException;
use Karolak\Core\Action\Cli\StandardInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(StandardInput::class),
    CoversClass(ArgumentNotFoundException::class),
    CoversClass(OptionNotFoundException::class)
]
final class StandardInputTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldReturnTrueWhenArgumentExists(): void
    {
        // given
        $arguments = ['arg1' => 'value1'];

        // when
        $result = new StandardInput($arguments)->hasArgument('arg1');

        // then
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testShouldReturnFalseWhenArgumentDoesNotExists(): void
    {
        // given
        $arguments = ['arg1' => 'value1'];

        // when
        $result = new StandardInput($arguments)->hasArgument('not_found');

        // then
        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     */
    public function testShouldGetArgument(): void
    {
        // given
        $arguments = ['arg1' => 'value1'];

        // when
        $result = new StandardInput($arguments)->getArgument('arg1');

        // then
        $this->assertEquals('value1', $result);
    }

    /**
     * @return void
     */
    public function testShouldGetAllArguments(): void
    {
        // given
        $arguments = ['arg1' => 'value1', 'arg2' => 'value2'];

        // when
        $result = new StandardInput($arguments)->getArguments();

        // then
        $this->assertEquals($arguments, $result);
    }

    /**
     * @return void
     */
    public function testShouldGetNoArguments(): void
    {
        // when
        $result = new StandardInput()->getArguments();

        // then
        $this->assertEmpty($result);
    }

    /**
     * @return void
     * @throws ArgumentNotFoundException
     */
    public function testShouldThrowExceptionWhenArgumentDoesNotExist(): void
    {
        // then
        $this->expectException(ArgumentNotFoundException::class);

        // when
        new StandardInput()->getArgument('arg1');
    }


    /**
     * @return void
     */
    public function testShouldReturnTrueWhenOptionExists(): void
    {
        // given
        $options = ['option1' => 'value1'];

        // when
        $result = new StandardInput([], $options)->hasOption('option1');

        // then
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testShouldReturnFalseWhenOptionDoesNotExists(): void
    {
        // given
        $options = ['option1' => 'value1'];

        // when
        $result = new StandardInput([], $options)->hasOption('not_found');

        // then
        $this->assertFalse($result);
    }

    /**
     * @return void
     * @throws OptionNotFoundException
     */
    public function testShouldGetOption(): void
    {
        // given
        $options = ['option1' => true, 'option2' => false, 'option3' => 'value1', 'option4' => ['a']];
        $input = new StandardInput([], $options);

        // when
        $resultTrue = $input->getOption('option1');
        $resultFalse = $input->getOption('option2');
        $resultString = $input->getOption('option3');
        $resultArray = $input->getOption('option4');

        // then
        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
        $this->assertEquals('value1', $resultString);
        $this->assertEquals(['a'], $resultArray);
    }

    /**
     * @return void
     */
    public function testShouldGetAllOptions(): void
    {
        // given
        $options = ['option1' => 'value1', 'option2' => 'value2'];

        // when
        $result = new StandardInput([], $options)->getOptions();

        // then
        $this->assertEquals($options, $result);
    }

    /**
     * @return void
     */
    public function testShouldGetNoOption(): void
    {
        // when
        $result = new StandardInput()->getOptions();

        // then
        $this->assertEmpty($result);
    }

    /**
     * @return void
     */
    public function testShouldThrowExceptionWhenOptionDoesNotExist(): void
    {
        // then
        $this->expectException(OptionNotFoundException::class);

        // when
        new StandardInput()->getOption('option1');
    }
}