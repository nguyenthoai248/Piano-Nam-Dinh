<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
class StreamingPlaceholderTemplateFactory implements StreamTemplateFactoryInterface
{
    /**
     * @var string
     */
    protected $tokenStart;
    /**
     * @var string
     */
    protected $tokenEnd;
    /**
     * @var string|null
     */
    protected $default;
    /**
     * @var StringStreamFactoryInterface
     */
    protected $streamFactory;
    public function __construct(string $tokenStart, string $tokenEnd, ?string $default, StringStreamFactoryInterface $streamFactory)
    {
        $this->tokenStart = $tokenStart;
        $this->tokenEnd = $tokenEnd;
        $this->default = $default;
        $this->streamFactory = $streamFactory;
    }
    /**
     * @inheritDoc
     */
    public function createTemplateFromStream(StreamInterface $stream) : TemplateInterface
    {
        return new StreamingPlaceholderTemplate($stream, $this->streamFactory, $this->tokenStart, $this->tokenEnd, $this->default);
    }
}
