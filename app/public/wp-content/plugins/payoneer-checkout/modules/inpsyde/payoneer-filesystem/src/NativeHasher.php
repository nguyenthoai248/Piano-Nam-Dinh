<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use HashContext;
use InvalidArgumentException;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
/**
 * A hasher that uses native PHP hashing features.
 *
 * @see HashContext
 */
class NativeHasher implements HasherInterface
{
    /**
     * @var int
     */
    protected $maxBufferSize;
    /**
     * @var HashContextFactoryInterface
     */
    protected $hashContextFactory;
    /**
     * @param HashContextFactoryInterface $hashContextFactory The factory used for creating hash contexts.
     * @param int $maxBufferSize How many bytes of source data to read at once.
     */
    public function __construct(HashContextFactoryInterface $hashContextFactory, int $maxBufferSize)
    {
        $this->hashContextFactory = $hashContextFactory;
        $this->maxBufferSize = $maxBufferSize;
    }
    /**
     * @inheritDoc
     */
    public function getHash(StreamInterface $stream) : string
    {
        if (!$stream->isReadable()) {
            throw new InvalidArgumentException('The stream must be readable for hashing');
        }
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        $context = $this->hashContextFactory->createHashContext();
        $context->init();
        $maxBufferSize = $this->maxBufferSize;
        while (!$stream->eof()) {
            $buffer = $stream->read($maxBufferSize);
            $context->update($buffer);
        }
        $stream->close();
        $hash = $context->finalize(\true);
        return $hash;
    }
}
