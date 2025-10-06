<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use RangeException;
use Syde\Vendor\SebastianBergmann\GlobalState\RuntimeException;
/**
 * Can create a template from a path.
 */
interface PathTemplateFactoryInterface
{
    /**
     * Creates a template from the given path.
     *
     * @param string $path The path to create the template from.
     *
     * @return TemplateInterface The new template.
     * @throws RangeException If path is invalid.
     * @throws RuntimeException If problem creating.
     */
    public function createTemplateFromPath(string $path) : TemplateInterface;
}
