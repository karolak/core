<?php

declare(strict_types=1);

namespace Karolak\Core\Application\Query;

use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

final readonly class AttributeBasedQueryBus implements QueryBusInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function ask(QueryInterface $query): QueryResultInterface
    {
        try {
            $attribute = new ReflectionClass($query::class)
                ->getAttributes(QueryFor::class)[0]
                ?? throw QueryHandlerNotFoundException::for($query::class);

            /** @var object $queryHandler */
            $queryHandler = $this->container->get($attribute->newInstance()->queryHandlerClass);
            return $queryHandler instanceof QueryHandlerInterface
                ? $queryHandler->handle($query)
                : throw InvalidQueryHandlerException::with($query::class, $queryHandler::class);
        } catch (ReflectionException | ContainerExceptionInterface $e) {
            throw QueryHandlerNotFoundException::for($query::class, $e);
        }
    }
}