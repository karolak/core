<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Domain;

use DomainException;
use Karolak\Core\Domain\AbstractAggregate;
use Karolak\Core\Domain\IdInterface;
use Karolak\Core\Domain\ReconstructAggregateTrait;
use Karolak\Core\Tests\Mock\DummyEvent;
use Karolak\Core\Tests\Mock\DummyId;
use Override;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversTrait(ReconstructAggregateTrait::class)]
final class ReconstructAggregateTraitTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldUseInitMethodAndApplyMethodWhenReconstructAggregate(): void
    {
        // given
        $id = new DummyId('1');
        $aggregate = new class($id) extends AbstractAggregate {
            use ReconstructAggregateTrait;

            /**
             * @param IdInterface $id
             * @param string $name
             */
            public function __construct(
                private readonly IdInterface $id,
                private(set) string $name = 'unmodified'
            ) {
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return $this->id;
            }

            /**
             * @return string
             */
            public function getName(): string
            {
                return $this->name;
            }

            /**
             * @param DummyEvent $event
             * @return static
             */
            static protected function initFromDummyEvent(DummyEvent $event): static
            {
                return new static(new DummyId('1'));
            }

            /**
             * @param DummyEvent $event
             * @return void
             */
            protected function applyDummyEvent(DummyEvent $event): void
            {
                $this->name = 'modified';
            }
        };

        // when
        $reconstructed = $aggregate::reconstruct([new DummyEvent(), new DummyEvent()]);

        // then
        $this->assertSame(strval($id), strval($reconstructed->getId()));
        $this->assertInstanceOf(AbstractAggregate::class, $reconstructed);
        $this->assertEquals('modified', $reconstructed->getName());
    }

    /**
     * @return void
     */
    public function testShouldThrowDomainExceptionWhenInitMethodIsMissing(): void
    {
        // then
        $this->expectException(DomainException::class);

        // given
        $id = new DummyId('1');
        $aggregate = new class($id) extends AbstractAggregate {
            use ReconstructAggregateTrait;

            /**
             * @param IdInterface $id
             */
            public function __construct(
                private readonly IdInterface $id
            ) {
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return $this->id;
            }
        };

        // when
        $aggregate::reconstruct([new DummyEvent()]);
    }

    /**
     * @return void
     */
    public function testShouldThrowDomainExceptionWhenApplyMethodIsMissing(): void
    {
        // then
        $this->expectException(DomainException::class);

        // given
        $id = new DummyId('1');
        $aggregate = new class($id) extends AbstractAggregate {
            use ReconstructAggregateTrait;

            /**
             * @param IdInterface $id
             */
            public function __construct(
                private readonly IdInterface $id
            ) {
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getId(): IdInterface
            {
                return $this->id;
            }

            /**
             * @param DummyEvent $event
             * @return static
             */
            static protected function initFromDummyEvent(DummyEvent $event): static
            {
                return new static(new DummyId('1'));
            }
        };

        // when
        $aggregate::reconstruct([new DummyEvent(), new DummyEvent()]);
    }
}