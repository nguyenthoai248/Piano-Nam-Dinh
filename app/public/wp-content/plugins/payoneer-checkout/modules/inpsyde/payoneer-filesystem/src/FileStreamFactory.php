<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * Can create a stream from a file.
 */
class FileStreamFactory implements FileStreamFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $path, string $mode) : StreamInterface
    {
        if (!file_exists($path) && strstr($mode, 'r')) {
            throw new RuntimeException(sprintf('"File at path "%1$s" does not exist', $path));
        }
        $dirname = pathinfo($path, \PATHINFO_DIRNAME);
        if (!file_exists($dirname)) {
            $this->createDirectory($dirname);
        }
        $handle = fopen($path, $mode);
        if (!$handle) {
            throw new RuntimeException(sprintf('Could not open file at path "%1$s" with mode "%2$s"', $path, $mode));
        }
        return new ResourceStream($handle);
    }
    /**
     * Create a directory at provided path.
     *
     * @param string $dirPath Directory to create.
     *
     * @throws RuntimeException If failed to create directory.
     */
    protected function createDirectory(string $dirPath) : void
    {
        $success = mkdir($dirPath, 0755, \true);
        if (!$success) {
            throw new RuntimeException(sprintf('Could not create directory %1$s', $dirPath));
        }
    }
}
