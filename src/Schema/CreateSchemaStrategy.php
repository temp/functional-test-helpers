<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Schema;

use Brainbits\FunctionalTestHelpers\Schema\Strategy\MysqlBasedSchemaStrategy;
use Brainbits\FunctionalTestHelpers\Schema\Strategy\MysqlDamaBasedSchemaStrategy;
use Brainbits\FunctionalTestHelpers\Schema\Strategy\SchemaStrategy;
use Brainbits\FunctionalTestHelpers\Schema\Strategy\SqliteFileBasedSchemaStrategy;
use Brainbits\FunctionalTestHelpers\Schema\Strategy\SqliteFileDamaBasedSchemaStrategy;
use Brainbits\FunctionalTestHelpers\Schema\Strategy\SqliteMemoryBasedSchemaStrategy;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

use function class_exists;
use function str_contains;
use function str_ends_with;

final class CreateSchemaStrategy
{
    public function __invoke(Connection $connection): SchemaStrategy
    {
        $params = $connection->getParams();

        if ($this->isSqLiteMemory($params)) {
            return new SqliteMemoryBasedSchemaStrategy();
        }

        if ($this->isSqLiteFile($params)) {
            if ($this->isDamaDoctrineCacheBundleActive($connection->getDriver())) {
                return new SqliteFileDamaBasedSchemaStrategy();
            }

            return new SqliteFileBasedSchemaStrategy();
        }

        if ($this->isMysql($params)) {
            if ($this->isDamaDoctrineCacheBundleActive($connection->getDriver())) {
                return new MysqlDamaBasedSchemaStrategy();
            }

            return new MysqlBasedSchemaStrategy();
        }

        throw NoSchemaStrategyFound::forConnectionParameters($params);
    }

    /** @param mixed[] $params */
    private function isSqLiteMemory(array $params): bool
    {
        $url = (string) ($params['url'] ?? '');
        $driver = (string) ($params['driver'] ?? '');
        $memory = (bool) ($params['memory'] ?? false);

        return (str_contains($url, 'sqlite:') && str_contains($url, ':memory:'))
            || ($memory === true && str_ends_with($driver, 'sqlite'));
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

    private function isDamaDoctrineCacheBundleActive(Driver|null $driver): bool
    {
        if (!class_exists(StaticDriver::class)) {
            return false;
        }

        return $driver instanceof StaticDriver && StaticDriver::isKeepStaticConnections();
    }
}
