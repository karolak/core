<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

interface QueryBusInterface
{
    /**
     * @param QueryInterface $query
     * @return QueryResultInterface
     * @throws QueryHandlerNotFoundException
     * @throws InvalidQueryHandlerException
     */
    public function ask(QueryInterface $query): QueryResultInterface;
}