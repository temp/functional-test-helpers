<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Doctrine\DBAL\Connection;

interface ApplySchema
{
    public function __invoke(SchemaBuilder $schemaBuilder, Connection $connection): void;
}
