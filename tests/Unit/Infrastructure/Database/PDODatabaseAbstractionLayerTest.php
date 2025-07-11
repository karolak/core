<?php

declare(strict_types=1);

namespace Karolak\Core\Tests\Unit\Infrastructure\Database;

use Karolak\Core\Application\Database\DisconnectedException;
use Karolak\Core\Application\Database\PDOFactoryInterface;
use Karolak\Core\Application\Database\StorageException;
use Karolak\Core\Application\Database\TransactionException;
use Karolak\Core\Infrastructure\Database\PDODatabaseAbstractionLayer;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(PDODatabaseAbstractionLayer::class),
    CoversClass(StorageException::class),
    CoversClass(DisconnectedException::class),
    CoversClass(TransactionException::class)
]
final class PDODatabaseAbstractionLayerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldExecuteQueryWithParams(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $statement
            ->expects($this->exactly(5))
            ->method('bindValue');
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute(
            'SELECT * FROM table WHERE a = :a AND b = :b AND c = :c AND d = :d AND e = :e',
            [':a' => null, ':b' => 1, ':c' => '1', ':d' => true, ':e' => 2.12]
        );
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testThrowExceptionWhenDisconnectedWhileExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $this->expectException(DisconnectedException::class);
        $statement
            ->expects($this->never())
            ->method('execute');
        $pdo
            ->expects($this->never())
            ->method('prepare');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->disconnect();
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testThrowExceptionWhenPrepareStatementReturnsFalseWhileExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->never())
            ->method('execute');
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn(false);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testThrowExceptionWhenPrepareStatementThrowsPDOExceptionWhileExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->never())
            ->method('execute');
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException(''));
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testThrowExceptionWhenExecuteStatementReturnsFalseWhileExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testThrowExceptionWhenExecuteStatementThrowsPDOExceptionWhileExecuteQuery(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException(''));
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->execute('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldFetch(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statement
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([]);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $data = $dal->fetch('SELECT * FROM table LIMIT 1');

        // then
        $this->assertEmpty($data);
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldThrowExceptionWhenFetchResultIsFalse(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        //then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statement
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->fetch('SELECT * FROM table LIMIT 1');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldFetchAll(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statement
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $data = $dal->fetchAll('SELECT * FROM table');

        // then
        $this->assertEmpty($data);
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     * @throws DisconnectedException
     */
    public function testShouldThrowExceptionWhenFetchAllThrowsPDOException(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        //then
        $this->expectException(StorageException::class);
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statement
            ->expects($this->once())
            ->method('fetchAll')
            ->willThrowException(new PDOException(''));
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->fetchAll('SELECT * FROM table');
    }

    /**
     * @return void
     * @throws Exception
     * @throws StorageException
     */
    public function testShouldReconnect(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $factory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->reconnect();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldBeginTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldBeginTransactionMoreThanOnce(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $statement
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SAVEPOINT level1')
            ->willReturn($statement);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->beginTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenDisconnectedWhileBeginTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(DisconnectedException::class);
        $pdo
            ->expects($this->never())
            ->method('beginTransaction');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->disconnect();
        $dal->beginTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenBeginTransactionReturnsFalse(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(false);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenBeginTransactionThrowsPDOException(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willThrowException(new PDOException(''));
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldBeginTransactionAndCommit(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldBeginTransactionAndCommitMoreThenOnce(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $pdo
            ->expects($this->exactly(2))
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->exactly(2))
            ->method('commit')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->commitTransaction();
        $dal->beginTransaction();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldCommitInnerTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $statement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $pdo
            ->expects($this->exactly(2))
            ->method('prepare')
            ->with($this->logicalOr(
                $this->equalTo('SAVEPOINT level1'),
                $this->equalTo('RELEASE SAVEPOINT level1')
            ))
            ->willReturn($statement);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->beginTransaction();
        $dal->commitTransaction();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenCommitWhileDisconnected(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(DisconnectedException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->never())
            ->method('commit');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->disconnect();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenCommitWithoutBeginTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->never())
            ->method('beginTransaction');
        $pdo
            ->expects($this->never())
            ->method('commit');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenCommitReturnsFalse(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('commit')
            ->willReturn(false);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenCommitThrowsException(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('commit')
            ->willThrowException(new PDOException(''));
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->commitTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldBeginTransactionAndRollback(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('rollback')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenRollbackWhileDisconnected(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(DisconnectedException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->never())
            ->method('rollback');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->disconnect();
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenRollbackWithoutBeginTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->never())
            ->method('beginTransaction');
        $pdo
            ->expects($this->never())
            ->method('rollback');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenRollbackReturnsFalse(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('rollback')
            ->willReturn(false);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldThrowExceptionWhenRollbackThrowsException(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(TransactionException::class);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('rollback')
            ->willThrowException(new PDOException(''));
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     * @throws TransactionException
     */
    public function testShouldRollbackInnerTransaction(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);

        // then
        $statement
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $pdo
            ->expects($this->exactly(2))
            ->method('prepare')
            ->with($this->logicalOr(
                $this->equalTo('SAVEPOINT level1'),
                $this->equalTo('ROLLBACK TO SAVEPOINT level1')
            ))
            ->willReturn($statement);
        $pdo
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->once())
            ->method('rollback')
            ->willReturn(true);
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->beginTransaction();
        $dal->beginTransaction();
        $dal->rollbackTransaction();
        $dal->rollbackTransaction();
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     */
    public function testShouldCheckIfTransactionIsOn(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $pdo
            ->expects($this->once())
            ->method('inTransaction')
            ->willReturn(true);
        $pdo
            ->expects($this->never())
            ->method('beginTransaction');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $this->assertTrue($dal->isInTransaction());
    }

    /**
     * @return void
     * @throws DisconnectedException
     * @throws Exception
     * @throws StorageException
     */
    public function testShouldThrowExceptionWhenCheckIfTransactionIsOnWhileDisconnected(): void
    {
        // given
        $factory = $this->createMock(PDOFactoryInterface::class);
        $pdo = $this->createMock(PDO::class);

        // then
        $this->expectException(DisconnectedException::class);
        $pdo
            ->expects($this->never())
            ->method('inTransaction');
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($pdo);

        // when
        $dal = new PDODatabaseAbstractionLayer($factory);
        $dal->disconnect();
        $dal->isInTransaction();
    }
}