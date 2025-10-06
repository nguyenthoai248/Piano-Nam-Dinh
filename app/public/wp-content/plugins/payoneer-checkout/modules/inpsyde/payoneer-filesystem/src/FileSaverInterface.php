<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
/**
 * Saves content to a file.
 */
interface FileSaverInterface
{
    /**
     * Saves given content to a file at the specified path.
     *
     * If the file does not exist, it will be created first.
     * Otherwise, it will be completely overwritten.
     *
     * @param string $path The path to the file.
     * @param StreamInterface $content The content to save.
     *
     * @throws RangeException If path is invalid.
     * @throws RuntimeException If problem saving.
     */
    public function saveFile(string $path, StreamInterface $content) : void;
}
