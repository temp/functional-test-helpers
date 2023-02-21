<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Doctrine\DBAL\Connection;

use function str_contains;
use function str_ends_with;

final class CreateApplySchema
{
    public function __invoke(Connection $connection): ApplySchema
    {
        $params = $connection->getParams();

        if ($this->isSqLiteMemory($params)) {
            return new SqliteMemoryBasedApplySchema();
        }

        if ($this->isSqLiteFile($params)) {
            return new SqliteFileBasedApplySchema();
        }

        if ($this->isMysql($params)) {
            return new MysqlBasedApplySchema();
        }

        throw NoApplySchemaStrategyFound::forConnectionParameters($params);
    }

    /** @param mixed[] $params */
    private function isSqLiteMemory(array $params): bool
    {
        $url = (string) ($params['url'] ?? '');
        $driver = (string) ($params['driver'] ?? '');
        $memory = (bool) ($params['memory'] ?? false);

        return str_contains($url, 'sqlite:') && str_contains($url, ':memory:')
            || $memory === true && str_ends_with($driver, 'sqlite');
    }

    /** @param mixed[] $params */
    private function isSqLiteFile(array $params): bool
    {
        $url = (string) ($params['url'] ?? '');
        $driver = (string) ($params['driver'] ?? '');
        $path = (string) ($params['path'] ?? '');

        return $path !== '' && (str_contains($url, 'sqlite:') || str_ends_with($driver, 'sqlite'));
    }

    /** @param mixed[] $params */
    private function isMysql(array $params): bool
    {
        $driver = (string) ($params['driver'] ?? '');

        return str_ends_with($driver, 'mysql');
    }
}
