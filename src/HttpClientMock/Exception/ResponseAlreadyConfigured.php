<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock\Exception;

use Brainbits\FunctionalTestHelpers\HttpClientMock\MockResponseBuilder;
use RuntimeException;
use Throwable;

use function Safe\sprintf;

use const PHP_EOL;

final class ResponseAlreadyConfigured extends RuntimeException
{
    public static function withAResponse(MockResponseBuilder $responseBuilder): self
    {
        return new self(sprintf('A request is already configured:%s%s', PHP_EOL, (string) $responseBuilder));
    }

    public static function withAnException(Throwable $exception): self
    {
        return new self(
            sprintf('An exception is already configured: %s (%s)', $exception::class, $exception->getMessage())
        );
    }
}
