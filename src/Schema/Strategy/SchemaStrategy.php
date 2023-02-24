<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema\Strategy;

use Brainbits\FunctionalTestHelpers\Schema\DataBuilder;
use Brainbits\FunctionalTestHelpers\Schema\SchemaBuilder;
use Doctrine\DBAL\Connection;

interface SchemaStrategy
{
    public function applySchema(SchemaBuilder $schemaBuilder, Connection $connection): void;

    public function deleteData(Connection $connection): void;

    public function resetSequences(Connection $connection): void;

    public function applyData(DataBuilder $dataBuilder, Connection $connection): void;
}
