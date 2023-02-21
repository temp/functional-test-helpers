<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/** @mixin TestCase */
trait SchemaTrait
{
    final protected function fixtureFromConnection( // @phpstan-ignore-line
        Connection $connection,
        SchemaBuilder $schemaBuilder,
        DataBuilder $dataBuilder,
        callable $buildData,
        bool $quoteDataTable = true,
    ): void {
        $buildData($dataBuilder);

        $this->applySchema($schemaBuilder, $connection);
        $this->applyData($dataBuilder, $connection, $quoteDataTable);
    }

    final protected function fixtureFromNewConnection( // @phpstan-ignore-line
        SchemaBuilder $schemaBuilder,
        DataBuilder $dataBuilder,
        callable $buildData,
        bool $quoteDataTable = true,
    ): Connection {
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            null,
            new EventManager(),
        );

        $this->fixtureFromConnection($connection, $schemaBuilder, $dataBuilder, $buildData, $quoteDataTable);

        return $connection;
    }

    /** @internal */
    private function applySchema(SchemaBuilder $schemaBuilder, Connection $connection): void
    {
        $applySchema = (new CreateApplySchema())($connection);

        $applySchema($schemaBuilder, $connection);
    }

    /** @internal */
    private function applyData(DataBuilder $dataBuilder, Connection $connection, bool $quoteDataTable = true): void
    {
        foreach ($dataBuilder->getData() as $table => $rows) {
            $table = $quoteDataTable ? $connection->quoteIdentifier($table) : $table;

            foreach ($rows as $row) {
                $row = $quoteDataTable ? $this->quoteKeys($connection, $row) : $row;

                $connection->insert($table, $row);
            }
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     *
     * @interal
     */
    private function quoteKeys(Connection $connection, mixed $row): array
    {
        $quotedRow = [];

        foreach ($row as $key => $value) {
            $quotedRow[$connection->quoteIdentifier($key)] = $value;
        }

        return $quotedRow;
    }
}
