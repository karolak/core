<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Domain;

use Karolak\Core\Domain\AbstractAggregate;
use Karolak\Core\Domain\IdInterface;
use Karolak\Core\Tests\Mock\EmptyEvent;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(AbstractAggregate::class)
]
final class AbstractAggregateTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testShouldRecordEventAndExtractIt(): void
    {
        // given
        $id = $this->createMock(IdInterface::class);
        $aggregate = new class($id) extends AbstractAggregate {
            /**
             * @param IdInterface $id
             */
            public function __construct(private(set) readonly IdInterface $id)
            {
            }

            /**
             * @return void
             */
            public function doSomething(): void
            {
                $this->recordEvent(new EmptyEvent());
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public static function reconstruct(IdInterface $id, array $events): static
            {
                return new static($id);
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
        $eventsBefore = $aggregate->releaseEvents();
        $aggregate->doSomething();
        $eventsAfter = $aggregate->releaseEvents();

        // then
        $this->assertCount(0, $eventsBefore);
        $this->assertCount(1, $eventsAfter);
        $this->assertCount(0, $aggregate->releaseEvents());
    }
}