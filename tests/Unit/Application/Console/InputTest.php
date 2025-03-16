<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Console;

use Karolak\Core\Application\Console\ArgumentNotFoundException;
use Karolak\Core\Application\Console\Input;
use Karolak\Core\Application\Console\OptionNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(Input::class),
    CoversClass(Input::class),
    CoversClass(ArgumentNotFoundException::class),
    CoversClass(OptionNotFoundException::class)
]
final class InputTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldReturnTrueWhenArgumentExists(): void
    {
        // given
        $arguments = ['arg1' => 'value1'];
        $input = new Input($arguments);

        // when
        $result = $input->hasArgument('arg1');

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
        $input = new Input($arguments);

        // when
        $result = $input->hasArgument('not_found');

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
        $input = new Input($arguments);

        // when
        $result = $input->getArgument('arg1');

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
        $input = new Input($arguments);

        // when
        $result = $input->getArguments();

        // then
        $this->assertEquals($arguments, $result);
    }

    /**
     * @return void
     */
    public function testShouldGetNoArguments(): void
    {
        // given
        $input = new Input();

        // when
        $result = $input->getArguments();

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

        // given
        $input = new Input();

        // when
        $input->getArgument('arg1');
    }


    /**
     * @return void
     */
    public function testShouldReturnTrueWhenOptionExists(): void
    {
        // given
        $options = ['option1' => 'value1'];
        $input = new Input([], $options);

        // when
        $result = $input->hasOption('option1');

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
        $input = new Input([], $options);

        // when
        $result = $input->hasOption('not_found');

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
        $input = new Input([], $options);

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
        $input = new Input([], $options);

        // when
        $result = $input->getOptions();

        // then
        $this->assertEquals($options, $result);
    }

    /**
     * @return void
     */
    public function testShouldGetNoOption(): void
    {
        // given
        $input = new Input();

        // when
        $result = $input->getOptions();

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

        // given
        $input = new Input();

        // when
        $input->getOption('option1');
    }
}