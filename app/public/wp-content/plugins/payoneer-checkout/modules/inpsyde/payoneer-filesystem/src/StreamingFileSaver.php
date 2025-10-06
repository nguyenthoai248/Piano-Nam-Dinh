<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use UnexpectedValueException;
/**
 * A file saver that uses a buffer to stream the input to the destination.
 */
class StreamingFileSaver implements FileSaverInterface
{
    /**
     * @var FileStreamFactoryInterface
     */
    protected $streamFactory;
    /**
     * @var int
     */
    protected $maxBufferSize;
    public function __construct(FileStreamFactoryInterface $streamFactory, int $maxBufferSize)
    {
        $this->streamFactory = $streamFactory;
        $this->maxBufferSize = $maxBufferSize;
    }
    /**
     * @inheritDoc
     */
    public function saveFile(string $path, StreamInterface $content) : void
    {
        $maxBufferSize = $this->maxBufferSize;
        if (!$content->isReadable()) {
            throw new RangeException('Cannot save from non-readable stream');
        }
        $destination = $this->createWritableStreamForPath($path);
        if ($content->isSeekable()) {
            $content->rewind();
        }
        while (!$content->eof()) {
            $buffer = $content->read($maxBufferSize);
            $destination->write($buffer);
        }
        $destination->close();
    }
    /**
     * Creates a new writable stream for the specified path.
     *
     * @param string $path The path to create a stream for.
     *
     * @return StreamInterface The new stream that is in a writable state.
     *
     * @throws RangeException If problem creating.
     */
    protected function createWritableStreamForPath(string $path) : StreamInterface
    {
        $product = $this->streamFactory->createStreamFromFile($path, 'w');
        if (!$product->isWritable()) {
            throw new UnexpectedValueException(sprintf('Stream created for path "%1$s" is not writable', $path));
        }
        return $product;
    }
}
