<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

use RuntimeException;

interface RepositoryInterface
{
    /**
     * @param AbstractAggregate $aggregate
     * @return void
     * @throws RuntimeException
     */
    public function persist(AbstractAggregate $aggregate): void;

    /**
     * @param IdInterface $id
     * @return AbstractAggregate
     * @throws RuntimeException
     */
    public function get(IdInterface $id): AbstractAggregate;
}