<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

interface DataBuilder
{
    public static function create(SchemaBuilder $schemaBuilder): self;

    /**
     * @return mixed[]
     */
    public function getData(): array;
}
