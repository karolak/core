<?php

declare(strict_types=1);

namespace Karolak\Core\Application\View;

use Override;
use RuntimeException;

final readonly class PlainPHPView implements ViewInterface
{
    /**
     * @param ViewConfigInterface $config
     */
    public function __construct(private ViewConfigInterface $config)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function render(string $template, array $parameters = []): string
    {
        $path = $this->config->getTemplatesDirectory() . DIRECTORY_SEPARATOR . $template . '.php';
        if (false === file_exists($path)) {
            throw new RuntimeException(sprintf('Template "%s" does not exist.', $path));
        }

        ob_start();
        extract($parameters);
        require $path;
        $contents = ob_get_contents();
        ob_end_clean();

        return false === $contents ? '' : $contents;
    }
}