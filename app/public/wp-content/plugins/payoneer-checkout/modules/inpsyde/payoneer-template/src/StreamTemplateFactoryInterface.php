<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
/**
 * Can create a template from a stream.
 */
interface StreamTemplateFactoryInterface
{
    /**
     * Creates a template from the given stream.
     *
     * @param StreamInterface $stream The stream to create the template from.
     *
     * @return TemplateInterface The new template.
     * @throws RangeException If stream is in an invalid state.
     * @throws RuntimeException If problem creating.
     */
    public function createTemplateFromStream(StreamInterface $stream) : TemplateInterface;
}
