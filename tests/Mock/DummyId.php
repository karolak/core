<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Mock;

use Karolak\Core\Domain\IdInterface;
use Override;
use Stringable;

final readonly class DummyId implements IdInterface
{
    /**
     * @param string $id
     */
    public function __construct(private string $id)
    {
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function fromString(Stringable|string $id): IdInterface
    {
        return new self(strval($id));
    }
}