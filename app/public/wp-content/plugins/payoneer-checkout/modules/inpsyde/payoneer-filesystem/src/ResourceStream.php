<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Exception;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
/**
 * A stream wrapper for a native PHP resource.
 */
class ResourceStream implements StreamInterface
{
    /** @var resource|closed-resource|null */
    protected $handle;
    /**
     * @param resource $handle A stream handle, like that returned by {@link fopen()}.
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
    }
    /**
     * @inheritDoc
     */
    public function __toString()
    {
        if ($this->handle === null || $this->getSize() === 0) {
            return '';
        }
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }
    /**
     * @inheritDoc
     */
    public function close()
    {
        $handle = $this->handle;
        if (is_resource($handle)) {
            fclose($handle);
        }
    }
    /**
     * @inheritDoc
     */
    public function detach()
    {
        $handle = $this->handle;
        $this->handle = null;
        return is_resource($handle) ? $handle : null;
    }
    /**
     * @inheritDoc
     */
    public function getSize()
    {
        $length = null;
        $handle = $this->handle;
        if (is_resource($handle)) {
            $stat = fstat($handle);
            if ($stat === \false) {
                return null;
            }
            /** @psalm-suppress RedundantCast */
            $length = (int) $stat['size'];
        }
        return $length;
    }
    /**
     * @inheritDoc
     */
    public function tell()
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            throw new RuntimeException('Cannot operate on a detached stream');
        }
        $position = ftell($handle);
        if ($position === \false) {
            throw new RuntimeException('Could not tell position in resource');
        }
        return $position;
    }
    /**
     * @inheritDoc
     */
    public function eof()
    {
        if ($this->handle === null) {
            return \true;
        }
        return $this->tell() === (int) $this->getSize();
    }
    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable') === \true;
    }
    /**
     * @inheritDoc
     *
     * @psalm-suppress MissingReturnType
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            throw new RuntimeException('Cannot operate on a detached stream');
        }
        try {
            fseek($handle, $offset, $whence);
        } catch (Exception $exc) {
            throw new RuntimeException('Could not change position in resource', 0, $exc);
        }
    }
    /**
     * @inheritDoc
     *
     * @psalm-suppress MissingReturnType
     */
    public function rewind()
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            throw new RuntimeException('Cannot operate on a detached stream');
        }
        $isRewound = rewind($handle);
        if (!$isRewound) {
            throw new RuntimeException('Could not rewind resource');
        }
    }
    /**
     * @inheritDoc
     */
    public function isWritable()
    {
        $mode = $this->getMetadata('mode');
        if (is_array($mode)) {
            return \false;
        }
        return stristr((string) $mode, 'w') !== \false;
    }
    /**
     * @inheritDoc
     */
    public function write($string)
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            throw new RuntimeException('Cannot operate on a detached stream');
        }
        $bytesWritten = fwrite($handle, $string);
        if ($bytesWritten === \false) {
            throw new RuntimeException(sprintf('Could not write %1$d bytes to resource', strlen($string)));
        }
        return $bytesWritten;
    }
    /**
     * @inheritDoc
     */
    public function isReadable()
    {
        $mode = $this->getMetadata('mode');
        if (is_array($mode)) {
            return \false;
        }
        $mode = (string) $mode;
        return stristr($mode, 'w+') !== \false || stristr($mode, 'r') !== \false;
    }
    /**
     * @inheritDoc
     */
    public function read($length)
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            throw new RuntimeException('Cannot operate on a detached stream');
        }
        if ($length < 0) {
            throw new RangeException(sprintf('Cannot read negative length "%1$d"', $length));
        }
        $contents = fread($handle, $length);
        if ($contents === \false) {
            throw new RuntimeException(sprintf('Could not read %1$d bytes from resource', $length));
        }
        return $contents;
    }
    /**
     * @inheritDoc
     */
    public function getContents()
    {
        return $this->read((int) $this->getSize() - $this->tell());
    }
    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        $handle = $this->handle;
        if (!is_resource($handle)) {
            return $key === null ? [] : null;
        }
        $metaData = stream_get_meta_data($handle);
        if ($key === null) {
            return $metaData;
        }
        return array_key_exists($key, $metaData) ? $metaData[$key] : null;
    }
}
