<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Doctrine\DBAL\Connection;

final class SqliteMemoryBasedApplySchema implements ApplySchema
{
    public function __invoke(SchemaBuilder $schemaBuilder, Connection $connection): void
    {
        foreach ($schemaBuilder->getSchema()->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
    }
}
