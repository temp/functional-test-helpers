<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Doctrine\DBAL\Schema\Schema;

interface SchemaBuilder
{
    public static function create(): self;

    public function getSchema(): Schema;
}
