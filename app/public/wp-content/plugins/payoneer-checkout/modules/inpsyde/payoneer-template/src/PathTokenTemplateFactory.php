<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FileStreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RuntimeException;
/**
 * Creates a template from a given stream.
 */
class PathTokenTemplateFactory implements PathTemplateFactoryInterface
{
    /**
     * @var FileStreamFactoryInterface
     */
    protected $fileStreamFactory;
    /**
     * @var StreamTemplateFactoryInterface
     */
    protected $streamTemplateFactory;
    public function __construct(FileStreamFactoryInterface $fileStreamFactory, StreamTemplateFactoryInterface $streamTemplateFactory)
    {
        $this->fileStreamFactory = $fileStreamFactory;
        $this->streamTemplateFactory = $streamTemplateFactory;
    }
    /**
     * @inheritDoc
     */
    public function createTemplateFromPath(string $path) : TemplateInterface
    {
        $fileStream = $this->fileStreamFactory->createStreamFromFile($path, 'r');
        $template = $this->createTemplateFromStream($fileStream);
        return $template;
    }
    /**
     * Creates a template from the given stream.
     *
     * @param StreamInterface $fileStream The stream to create a new template from.
     *
     * @return TemplateInterface The new template.
     *
     * @throws RuntimeException If problem creating.
     */
    protected function createTemplateFromStream(StreamInterface $fileStream) : TemplateInterface
    {
        return $this->streamTemplateFactory->createTemplateFromStream($fileStream);
    }
}
