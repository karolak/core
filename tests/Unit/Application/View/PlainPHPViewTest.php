<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\View;

use Karolak\Core\Application\View\PlainPHPView;
use Karolak\Core\Application\View\ViewConfigInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(PlainPHPView::class)]
final class PlainPHPViewTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldRenderView(): void
    {
        // given
        $view = new PlainPHPView($this->getConfig());

        // when
        $result = $view->render('template');

        // then
        $this->assertEquals('This is example.', $result);
    }

    /**
     * @return void
     */
    public function testShouldRenderViewWithVariables(): void
    {
        // given
        $view = new PlainPHPView($this->getConfig());

        // when
        $result = $view->render('template', ['example' => 'test']);

        // then
        $this->assertEquals('This is test.', $result);
    }

    /**
     * @return void
     */
    public function testShouldThrowExceptionWhenTemplateFileDoesNotExists(): void
    {
        // then
        $this->expectException(RuntimeException::class);

        // given
        $view = new PlainPHPView($this->getConfig());

        // then
        $view->render('not_found');
    }

    /**
     * @return ViewConfigInterface
     */
    private function getConfig(): ViewConfigInterface
    {
        return new readonly class() implements ViewConfigInterface {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getTemplatesDirectory(): string
            {
                return __DIR__;
            }
        };
    }
}