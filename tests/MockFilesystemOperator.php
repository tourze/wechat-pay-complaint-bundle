<?php

namespace WechatPayComplaintBundle\Tests;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemOperator;

class MockFilesystemOperator implements FilesystemOperator
{
    public function fileExists(string $location): bool
    {
        return false;
    }

    public function has(string $location): bool
    {
        return false;
    }

    public function read(string $location): string
    {
        return '';
    }

    /**
     * @return resource
     */
    public function readStream(string $location)
    {
        $resource = fopen('php://memory', 'r');
        if (false === $resource) {
            throw new \InvalidArgumentException('Failed to open memory stream'); // @phpstan-ignore-line
        }

        return $resource;
    }

    public function write(string $location, string $contents, array $config = []): void // @phpstan-ignore-line
    {
    }

    public function writeStream(string $location, $contents, array $config = []): void // @phpstan-ignore-line
    {
    }

    public function delete(string $location): void
    {
    }

    public function deleteDirectory(string $location): void
    {
    }

    public function createDirectory(string $location, array $config = []): void // @phpstan-ignore-line
    {
    }

    public function listContents(string $location, bool $deep = false): DirectoryListing
    {
        return new DirectoryListing([]);
    }

    public function move(string $source, string $destination, array $config = []): void // @phpstan-ignore-line
    {
    }

    public function copy(string $source, string $destination, array $config = []): void // @phpstan-ignore-line
    {
    }

    public function lastModified(string $location): int
    {
        return 0;
    }

    public function fileSize(string $location): int
    {
        return 0;
    }

    public function mimeType(string $location): string
    {
        return '';
    }

    public function visibility(string $location): string
    {
        return '';
    }

    public function setVisibility(string $location, string $visibility): void
    {
    }

    public function directoryExists(string $location): bool
    {
        return false;
    }
}
