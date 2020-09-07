<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\ZipContents;

use Countable;
use Generator;
use IteratorAggregate;

use function array_key_exists;
use function array_values;
use function count;

final class ZipInfo implements Countable, IteratorAggregate
{
    private int $size;
    private ?string $comment;
    /** @var FileInfo[] */
    private array $files;

    /**
     * @param FileInfo[] $files
     */
    private function __construct(int $size, ?string $comment, array $files)
    {
        $this->size = $size;
        $this->comment = $comment;
        $this->files = $files;
    }

    /**
     * @param mixed[]    $header
     * @param FileInfo[] $files
     */
    public static function fromCentralDirHeader(int $size, array $header, array $files): self
    {
        $mappedFiles = [];
        foreach ($files as $file) {
            $mappedFiles[$file->getPath()] = $file;
        }

        return new self($size, $header['comment'], $mappedFiles);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return FileInfo[]
     */
    public function getFiles(): array
    {
        return array_values($this->files);
    }

    public function hasFile(string $path): bool
    {
        return array_key_exists($path, $this->files);
    }

    public function getFile(string $path): ?FileInfo
    {
        if (!$this->hasFile($path)) {
            return null;
        }

        return $this->files[$path];
    }

    public function getIterator(): Generator
    {
        yield from $this->files;
    }

    public function count(): int
    {
        return count($this->files);
    }
}
