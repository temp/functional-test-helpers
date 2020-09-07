<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use Brainbits\FunctionalTestHelpers\HttpClientMock\Exception\NoMatchingMockRequest;
use Monolog\Handler\AbstractProcessingHandler;
use PHPUnit\Framework\Assert;

final class NoMatchingMockRequestHandler extends AbstractProcessingHandler
{
    public function clear(): void
    {
    }

    public function reset(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        if (!($record['context']['exception'] ?? null)) {
            return;
        }

        $exception = $record['context']['exception'];
        while (!($exception instanceof NoMatchingMockRequest) && $exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        if (!$exception instanceof NoMatchingMockRequest) {
            return;
        }

        // instanceof NoMatchingMockRequest

        $message = $record['context']['exception']->getMessage();

        if (!$message) {
            return;
        }

        Assert::fail($message);
    }
}
