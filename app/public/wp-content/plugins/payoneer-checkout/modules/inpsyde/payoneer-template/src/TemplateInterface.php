<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use Syde\Vendor\Psr\Http\Message\StreamInterface;
use Stringable;
/**
 * Can render itself with a context.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
interface TemplateInterface
{
    /**
     * Render itself with a context.
     *
     * @param array<string, mixed> $context The context.
     * @psalm-param Context $context
     *
     * @return StreamInterface The result of rendering.
     */
    public function render(array $context) : StreamInterface;
}
