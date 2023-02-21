<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Tests\Schema;

use ArrayObject;
use Brainbits\FunctionalTestHelpers\Schema\MysqlBasedApplySchema;
use Brainbits\FunctionalTestHelpers\Schema\SchemaBuilder;
use Brainbits\FunctionalTestHelpers\Snapshot\SnapshotTrait;
use Doctrine\DBAL\Cache\ArrayResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

use function func_get_arg;
use function Safe\preg_match;
use function str_starts_with;

/** @covers \Brainbits\FunctionalTestHelpers\Schema\MysqlBasedApplySchema */
final class MysqlBasedApplySchemaTest extends TestCase
{
    use SnapshotTrait;

    private MySQLPlatform $platform;
    private SchemaBuilder $schemaBuilder;

    protected function setUp(): void
    {
        $this->platform = new MySQLPlatform();

        $this->schemaBuilder = $this->createSchemaBuilder();
        $this->schemaBuilder->foo();
    }

    public function testApplySchema(): void
    {
        /** @phpstan-var ArrayObject<string, mixed[]> $queryLog */
        $queryLog = new ArrayObject();

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback($this->platform->quoteIdentifier(...));
        $connection->expects($this->any())
            ->method('getParams')
            ->willReturn(['driver' => 'pdo_mysql']);
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->platform);
        $connection->expects($this->any())
            ->method('executeStatement')
            ->willReturnCallback(
                static function () use ($queryLog): void {
                    $queryLog[] = ['statement' => func_get_arg(0)];
                },
            );
        $connection->expects($this->any())
            ->method('executeQuery')
            ->willReturnCallback(
                static function () use ($queryLog, $connection) {
                    $query = func_get_arg(0);
                    $result = [];

                    $queryLog[] = ['query' => $query, 'result' => $result];

                    return new Result(new ArrayResult($result), $connection);
                },
            );

        $applySchema = new MysqlBasedApplySchema(resetExecutedStatements: true);
        $applySchema($this->schemaBuilder, $connection);

        $this->assertMatchesArraySnapshot($queryLog->getArrayCopy());
    }

    public function testExistingTablesAreDroppedBeforeCreatingFreshSchema(): void
    {
        /** @phpstan-var ArrayObject<string, mixed[]> $queryLog */
        $queryLog = new ArrayObject();

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback($this->platform->quoteIdentifier(...));
        $connection->expects($this->any())
            ->method('getParams')
            ->willReturn(['driver' => 'pdo_mysql']);
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->platform);
        $connection->expects($this->any())
            ->method('executeStatement')
            ->willReturnCallback(
                static function () use ($queryLog): void {
                    $queryLog[] = ['statement' => func_get_arg(0)];
                },
            );
        $connection->expects($this->any())
            ->method('executeQuery')
            ->willReturnCallback(
                static function () use ($queryLog, $connection) {
                    $query = func_get_arg(0);

                    $result = [];
                    if (str_starts_with($query, 'SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')) {
                        // two old tables exists
                        $result = [['name' => 'old_table_1'], ['name' => 'old_table_2']];
                    } elseif (preg_match('/SELECT.*CONSTRAINT_NAME.*old_table_1/', $query)) {
                        // "old_table_1" has two constraints
                        $result = [['name' => 'constraint_1'], ['name' => 'constraint_2']];
                    }

                    $queryLog[] = ['query' => $query, 'result' => $result];

                    return new Result(new ArrayResult($result), $connection);
                },
            );

        $applySchema = new MysqlBasedApplySchema(resetExecutedStatements: true);
        $applySchema($this->schemaBuilder, $connection);

        $applySchema = new MysqlBasedApplySchema(resetExecutedStatements: false);

        $this->assertMatchesArraySnapshot($queryLog->getArrayCopy());
    }

    public function testSchemaIsReadFromCacheIfDatabaseAndCacheExists(): void
    {
        /** @phpstan-var ArrayObject<string, mixed[]> $queryLog */
        $queryLog = new ArrayObject();

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback($this->platform->quoteIdentifier(...));
        $connection->expects($this->any())
            ->method('getParams')
            ->willReturn(['driver' => 'pdo_mysql']);
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn(new MySQLPlatform());
        $connection->expects($this->any())
            ->method('executeStatement')
            ->willReturnCallback(
                static function () use ($queryLog): void {
                    $queryLog[] = ['statement' => func_get_arg(0)];
                },
            );
        $connection->expects($this->any())
            ->method('executeQuery')
            ->willReturnCallback(
                static function () use ($queryLog, $connection) {
                    $query = func_get_arg(0);
                    $result = [];

                    $queryLog[] = ['query' => $query, 'result' => $result];

                    return new Result(new ArrayResult($result), $connection);
                },
            );

        $applySchema = new MysqlBasedApplySchema(resetExecutedStatements: true);
        $applySchema($this->schemaBuilder, $connection);

        $applySchema = new MysqlBasedApplySchema(resetExecutedStatements: false);
        $applySchema($this->schemaBuilder, $connection);

        $this->assertMatchesArraySnapshot($queryLog->getArrayCopy());
    }

    private function createSchemaBuilder(): SchemaBuilder
    {
        return new class implements SchemaBuilder {
            private Schema $schema;

            public function __construct()
            {
                $this->schema = new Schema();
            }

            public static function create(): SchemaBuilder
            {
                return new self();
            }

            public function getSchema(): Schema
            {
                return $this->schema;
            }

            public function foo(): void
            {
                $table = $this->schema->createTable('foo');
                $table->addColumn('bar', 'string');
            }
        };
    }

    private function snapshotPath(): string
    {
        return __DIR__;
    }
}
