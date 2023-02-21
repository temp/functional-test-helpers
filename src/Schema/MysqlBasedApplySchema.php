<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use ArrayObject;
use Doctrine\DBAL\Connection;

use function count;
use function sprintf;

final class MysqlBasedApplySchema implements ApplySchema
{
    public static ArrayObject $executedStatements;

    public function __construct(bool $resetExecutedStatements = false)
    {
        if ($resetExecutedStatements) {
            self::$executedStatements = new ArrayObject();
        }

        self::$executedStatements ??= new ArrayObject();
    }

    public function __invoke(SchemaBuilder $schemaBuilder, Connection $connection): void
    {
        $platform = $connection->getDatabasePlatform();

        $existingTables = $connection->executeQuery($platform->getListTablesSQL())->fetchFirstColumn();

        if (count($existingTables) > 0) {
            if (count(self::$executedStatements) === 0) {
                $this->dropTables($existingTables, $connection);

                self::$executedStatements = new ArrayObject();
            } else {
                $this->deleteData($existingTables, $connection);
            }
        }

        foreach ($schemaBuilder->getSchema()->toSql($platform) as $sql) {
            if (self::$executedStatements->offsetExists($sql)) {
                continue;
            }

            self::$executedStatements[$sql] = $connection->executeStatement($sql);
        }
    }

    /** @param list<string> $tables */
    private function dropTables(array $tables, Connection $connection): void
    {
        $platform = $connection->getDatabasePlatform();

        try {
            $connection->executeStatement('SET foreign_key_checks = 0');

            foreach ($tables as $table) {
                $listForeignKeysSql = $platform->getListTableForeignKeysSQL($table);
                $foreignKeys = $connection->executeQuery($listForeignKeysSql)->fetchFirstColumn();

                foreach ($foreignKeys as $foreignKey) {
                    $dropForeignKeySQL = $platform->getDropForeignKeySQL($foreignKey, $table);
                    $connection->executeStatement($dropForeignKeySQL);
                }

                $dropTableSQL = $platform->getDropTableSQL($table);
                $connection->executeStatement($dropTableSQL);
            }
        } finally {
            $connection->executeStatement('SET foreign_key_checks = 1');
        }
    }

    /** @param list<string> $tables */
    private function deleteData(array $tables, Connection $connection): void
    {
        try {
            $connection->executeStatement('SET foreign_key_checks = 0');

            foreach ($tables as $table) {
                $quotedTableName = $connection->quoteIdentifier($table);

                $connection->executeStatement(sprintf('DELETE FROM %s', $quotedTableName));
                $connection->executeStatement(sprintf('ALTER TABLE %s AUTO_INCREMENT = 0', $quotedTableName));
            }
        } finally {
            $connection->executeStatement('SET foreign_key_checks = 1');
        }
    }
}
