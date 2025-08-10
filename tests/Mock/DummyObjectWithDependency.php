<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

readonly class DummyObjectWithDependency
{
    /**
     * @param DummyInterface $object
     */
    public function __construct(private(set) DummyInterface $object)
    {
    }
}