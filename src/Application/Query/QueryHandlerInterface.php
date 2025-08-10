<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

interface QueryHandlerInterface
{
    /**
     * @param QueryInterface $query
     * @return QueryResultInterface
     */
    public function handle(QueryInterface $query): QueryResultInterface;
}