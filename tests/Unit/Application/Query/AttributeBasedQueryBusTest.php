<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Query;

use Karolak\Core\Application\Query\AttributeBasedQueryBus;
use Karolak\Core\Application\Query\InvalidQueryHandlerException;
use Karolak\Core\Application\Query\QueryFor;
use Karolak\Core\Application\Query\QueryHandlerInterface;
use Karolak\Core\Application\Query\QueryHandlerNotFoundException;
use Karolak\Core\Application\Query\QueryInterface;
use Karolak\Core\Application\Query\QueryResultInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[
    CoversClass(AttributeBasedQueryBus::class),
    CoversClass(QueryFor::class),
    CoversClass(QueryHandlerNotFoundException::class),
    COversClass(InvalidQueryHandlerException::class)
]
final class AttributeBasedQueryBusTest extends TestCase
{
    /**
     * @return void
     * @throws QueryHandlerNotFoundException
     * @throws Exception
     * @throws InvalidQueryHandlerException
     */
    public function testShouldAskQuery(): void
    {
        // given
        $queryHandler = $this->createMock(QueryHandlerInterface::class);
        $queryResult = $this->createMock(QueryResultInterface::class);
        $queryHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(QueryInterface::class))
            ->willReturn($queryResult);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(QueryHandlerInterface::class)
            ->willReturn($queryHandler);

        $queryBus = new AttributeBasedQueryBus($container);

        // when
        $query = new #[QueryFor(QueryHandlerInterface::class)] class implements QueryInterface {};
        $queryBus->ask($query);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidQueryHandlerException
     */
    public function testShouldThrowExceptionWhenAttributeIsMissing(): void
    {
        // then
        $this->expectException(QueryHandlerNotFoundException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->never())
            ->method('get');

        $queryBus = new AttributeBasedQueryBus($container);

        // when
        $query = new class implements QueryInterface {};
        $queryBus->ask($query);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidQueryHandlerException
     */
    public function testShouldThrowExceptionWhenQueryHandlerNotFound(): void
    {
        // then
        $this->expectException(QueryHandlerNotFoundException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(QueryHandlerInterface::class)
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $queryBus = new AttributeBasedQueryBus($container);

        // when
        $query = new #[QueryFor(QueryHandlerInterface::class)] class implements QueryInterface {};
        $queryBus->ask($query);
    }

    /**
     * @return void
     * @throws QueryHandlerNotFoundException
     * @throws Exception
     * @throws InvalidQueryHandlerException
     */
    public function testShouldThrowExceptionWhenHandlerDoesNotImplementQueryHandlerInterface(): void
    {
        // then
        $this->expectException(InvalidQueryHandlerException::class);

        // given
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with(QueryhandlerInterface::class)
            ->willReturn($this->createMock(QueryInterface::class));

        $queryBus = new AttributeBasedQueryBus($container);

        // when
        $query = new #[QueryFor(QueryHandlerInterface::class)] class implements QueryInterface {};
        $queryBus->ask($query);
    }
}