<?php

declare(strict_types=1);

namespace Karolak\Core\Domain;

interface RepositoryInterface
{
    /**
     * @param AbstractAggregate $aggregate
     * @return void
     */
    public function persist(AbstractAggregate $aggregate): void;

    /**
     * @param IdInterface $id
     * @return AbstractAggregate
     */
    public function get(IdInterface $id): AbstractAggregate;
}