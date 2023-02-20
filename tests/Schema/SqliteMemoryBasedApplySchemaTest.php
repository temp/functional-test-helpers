<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Tests\Schema;

use Brainbits\FunctionalTestHelpers\Schema\SchemaBuilder;
use Brainbits\FunctionalTestHelpers\Schema\SqliteMemoryBasedApplySchema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

/** @covers \Brainbits\FunctionalTestHelpers\Schema\SqliteMemoryBasedApplySchema */
final class SqliteMemoryBasedApplySchemaTest extends TestCase
{
    public function testApplySchema(): void
    {
        $schemaBuilder = $this->createSchemaBuilder();
        $schemaBuilder->foo();

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn(new SqlitePlatform());
        $connection->expects($this->once())
            ->method('executeStatement')
            ->with('CREATE TABLE foo (bar VARCHAR(255) NOT NULL)');

        $applySchema = new SqliteMemoryBasedApplySchema();
        $applySchema($schemaBuilder, $connection);
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
}
