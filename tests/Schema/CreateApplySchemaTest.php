<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Tests\Schema;

use Brainbits\FunctionalTestHelpers\Schema\CreateApplySchema;
use Brainbits\FunctionalTestHelpers\Schema\NoApplySchemaStrategyFound;
use Brainbits\FunctionalTestHelpers\Schema\SqliteFileBasedApplySchema;
use Brainbits\FunctionalTestHelpers\Schema\SqliteMemoryBasedApplySchema;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

/** @covers \Brainbits\FunctionalTestHelpers\Schema\SqliteFileBasedApplySchema */
final class CreateApplySchemaTest extends TestCase
{
    public function testItUsesSqliteMemoryStrategyIfConfiguredByUrl(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getParams')
            ->willReturn(['url' => 'sqlite://:memory:']);

        $applySchema = (new CreateApplySchema())($connection);

        $this->assertInstanceOf(SqliteMemoryBasedApplySchema::class, $applySchema);
    }

    public function testItUsesSqliteMemoryStrategyIfConfiguredByParams(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getParams')
            ->willReturn(['driver' => 'pdo_sqlite', 'memory' => true]);

        $applySchema = (new CreateApplySchema())($connection);

        $this->assertInstanceOf(SqliteMemoryBasedApplySchema::class, $applySchema);
    }

    public function testItUsesSqliteFileStrategyIfConfiguredByUrl(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getParams')
            ->willReturn(['url' => 'sqlite:///tmp/my.db', 'path' => '/tmp/my.db']);

        $applySchema = (new CreateApplySchema())($connection);

        $this->assertInstanceOf(SqliteFileBasedApplySchema::class, $applySchema);
    }

    public function testItUsesSqliteStrategyIfConfiguredByParams(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getParams')
            ->willReturn(['driver' => 'pdo_sqlite', 'path' => '/tmp/my.db']);

        $applySchema = (new CreateApplySchema())($connection);

        $this->assertInstanceOf(SqliteFileBasedApplySchema::class, $applySchema);
    }

    public function testItThrowsException(): void
    {
        $this->expectException(NoApplySchemaStrategyFound::class);
        $this->expectExceptionMessage('No apply schema strategy found for connection parameters: {"key":"value"}');

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getParams')
            ->willReturn(['key' => 'value']);

        $applySchema = (new CreateApplySchema())($connection);

        $this->assertInstanceOf(SqliteFileBasedApplySchema::class, $applySchema);
    }
}
