<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * @mixin WebTestCase
 */
trait CommandTrait
{
    /**
     * @param mixed[] $input
     * @param mixed[] $options
     */
    final protected function runCommand(string $name, array $input = [], array $options = []): CommandTester
    {
        $application = new Application(self::$kernel);
        $command = $application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    final protected static function assertConsoleOutputContains(string $needle, CommandTester $commandTester): void
    {
        @trigger_error('Use assertCommandOutputContains() instead of assertConsoleOutputContains()', E_USER_DEPRECATED);

        self::assertCommandOutputContains($needle, $commandTester);
    }

    final protected static function assertConsoleStatusCodeSame(int $expectedCode, CommandTester $commandTester): void
    {
        @trigger_error('Use assertCommandStatusCodeSame() instead of assertConsoleStatusCodeSame()', E_USER_DEPRECATED);

        self::assertCommandStatusCodeSame($expectedCode, $commandTester);
    }

    final protected static function assertCommandOutputContains(string $needle, CommandTester $commandTester): void
    {
        self::assertStringContainsString($needle, $commandTester->getDisplay());
    }

    final protected static function assertCommandStatusCodeSame(int $expectedCode, CommandTester $commandTester): void
    {
        self::assertSame($expectedCode, $commandTester->getStatusCode());
    }
}
