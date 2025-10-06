<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * A stream that exposes access to a string.
 */
class StringStream implements StreamInterface
{
    /** @var string|null */
    private $data;
    /** @var int */
    private $pointer = 0;
    /** @var int */
    private $length;
    public function __construct(string $data)
    {
        $this->data = $data;
        $this->length = strlen($data);
    }
    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->data === null ? '' : $this->data;
    }
    /**
     * @inheritDoc
     */
    public function close() : void
    {
        $this->data = null;
        $this->pointer = 0;
        $this->length = 0;
    }
    /**
     * @inheritDoc
     */
    public function detach()
    {
        $this->data = null;
        $this->pointer = 0;
        $this->length = 0;
        return null;
    }
    /**
     * @inheritDoc
     */
    public function getSize() : int
    {
        return $this->length;
    }
    /**
     * @inheritDoc
     */
    public function tell() : int
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        return $this->pointer;
    }
    /**
     * @inheritDoc
     */
    public function eof() : bool
    {
        return $this->pointer === $this->length;
    }
    /**
     * @inheritDoc
     */
    public function isSeekable() : bool
    {
        return $this->data !== null;
    }
    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = \SEEK_SET) : void
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        switch ($whence) {
            case \SEEK_SET:
                $this->pointer = $offset;
                return;
            case \SEEK_CUR:
                $this->pointer = $this->pointer + $offset;
                return;
            case \SEEK_END:
                $this->pointer = $this->length + $offset;
                return;
        }
    }
    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        $this->pointer = 0;
    }
    /**
     * @inheritDoc
     */
    public function isWritable() : bool
    {
        return $this->data !== null;
    }
    /**
     * @inheritDoc
     */
    public function write($string) : int
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        // If we're at the end of the data, we can just append.
        if ($this->eof()) {
            $this->length += strlen($string);
            $this->data .= $string;
            $this->pointer = $this->length;
            return strlen($string);
        }
        // If we're purely overwriting, we can do that with substr.
        // If we have more to write than we can fit, we'll just substr the start and then concatenate the rest.
        $start = $this->getSubstring($this->data, 0, $this->pointer);
        $end = $this->getSubstring($this->data, $this->pointer + strlen($string), null);
        $this->data = "{$start}{$string}{$end}";
        // Since we can do both overwriting and appending here, we'll just recalculate:
        $this->length = strlen($this->data);
        $this->pointer = $this->length;
        return strlen($string);
    }
    /**
     * @inheritDoc
     */
    public function isReadable() : bool
    {
        return $this->data !== null;
    }
    /**
     * @inheritDoc
     */
    public function read($length) : string
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        $slice = $this->getSubstring($this->data, $this->pointer, $length);
        $this->pointer = $this->pointer + strlen($slice);
        return $slice;
    }
    /**
     * @inheritDoc
     */
    public function getContents() : string
    {
        if ($this->data === null) {
            throw new RuntimeException('Stream is detached');
        }
        return $this->read($this->length - $this->pointer);
    }
    /**
     * @inheritDoc
     */
    public function getMetadata($key = null) : ?array
    {
        return null;
    }
    /**
     * Retrieves a part of the specified string.
     *
     * @see substr()
     *
     * @param string $string The string to retrieve a part of.
     * @param int $offset The zero-based position to start the part from.
     * @param int|null $length The length of the part, if not null;
     *                         otherwise, remainder of the string.
     *
     * @return string The part of the string.
     *
     * @throws RuntimeException If problem retrieving.
     */
    protected function getSubstring(string $string, int $offset, ?int $length) : string
    {
        $result = substr($string, $offset, $length);
        if ($result === \false) {
            throw new RuntimeException(sprintf('Could not extract substring of "%1$s" with offset "%2$d" and length "%3$s"', $string, $offset, $length ?? 'null'));
        }
        return $result;
    }
}
