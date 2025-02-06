<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

readonly class ObjectWithDependency
{
    /**
     * @param EmptyInterface $object
     */
    public function __construct(private(set) EmptyInterface $object)
    {
    }
}