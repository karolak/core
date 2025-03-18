<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Application\Database;

use Karolak\Core\Application\Database\DatabaseConfigInterface;
use Karolak\Core\Application\Database\PDOFactory;
use Karolak\Core\Application\Database\StorageException;
use Override;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[
    UsesClass(PDOFactory::class),
    UsesClass(StorageException::class),
    CoversClass(PDOFactory::class)
]
final class PDOFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testShouldCreatePDOObject(): void
    {
        // given
        $factory = new PDOFactory(
            $this->getConfig(
                'sqlite::memory:',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 'a' => 'b']
            )
        );

        // when
        $pdo = $factory->create();

        // then
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertSame('sqlite', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @return void
     */
    public function testShouldThrowExceptionWhenDriverNotFound(): void
    {
        // then
        $this->expectException(StorageException::class);

        // given
        $factory = new PDOFactory(
            $this->getConfig('tt::memory:')
        );

        // when
        $factory->create();
    }


    /**
     * @param string $connectionString
     * @param array<int|string,mixed> $options
     * @return DatabaseConfigInterface
     */
    private function getConfig(string $connectionString, array $options = []): DatabaseConfigInterface
    {
        return new readonly class($connectionString, $options) implements DatabaseConfigInterface {
            /**
             * @param string $connectionString
             * @param array<int|string,mixed> $options
             */
            public function __construct(
                private string $connectionString,
                private array $options
            ) {
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getConnectionString(): string
            {
                return $this->connectionString;
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getDriverOptions(): array
            {
                return $this->options;
            }
        };
    }
}