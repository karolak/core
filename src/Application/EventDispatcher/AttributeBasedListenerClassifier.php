<?php

declare(strict_types=1);

namespace Karolak\Core\Application\EventDispatcher;

use Override;
use ReflectionClass;
use ReflectionException;

final readonly class AttributeBasedListenerClassifier implements ListenerClassifierInterface
{
    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    #[Override]
    public function groupByEvents(array $listeners): array
    {
        $result = [];
        foreach ($listeners as $listener) {
            $attributes = new ReflectionClass($listener)->getAttributes(ListenerFor::class);
            foreach ($attributes as $attribute) {
                $result[$attribute->newInstance()->eventClass][] = $listener;
            }
        }

        return $result;
    }
}