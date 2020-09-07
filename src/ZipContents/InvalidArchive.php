<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\ZipContents;

use RuntimeException;

use function gettype;
use function Safe\sprintf;

final class InvalidArchive extends RuntimeException
{
    /**
     * @param mixed $path
     */
    public static function notAFile($path): self
    {
        return new self(sprintf('Path %s is not valid', $path));
    }

    /**
     * @param mixed $stream
     */
    public static function notAStream($stream): self
    {
        return new self(sprintf('Valid stream is required, %s given', gettype($stream)));
    }

    public static function zeroSize(): self
    {
        return new self('ZIPs with size zero are not supported');
    }
}
