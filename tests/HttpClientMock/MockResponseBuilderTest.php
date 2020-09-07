<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use PHPUnit\Framework\TestCase;

use function implode;

use const PHP_EOL;

final class MockResponseBuilderTest extends TestCase
{
    public function testConvertableToString(): void
    {
        $builder = (new MockResponseBuilder())
            ->code(200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Language', 'de')
            ->json(['json' => 'content']);

        $parts = [
            'HTTP Code: 200',
            'Content-Type: application/json',
            'Content-Language: de',
            '',
            '{"json":"content"}',
        ];

        self::assertSame(implode(PHP_EOL, $parts), (string) $builder);
    }
}
