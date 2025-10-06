<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

/**
 * A service able to create a Style instance.
 */
interface StyleFactoryInterface
{
    /**
     * @param string|null $language Language to be used for displaying payment fields.
     *
     * @return StyleInterface Created Style instance.
     */
    public function createStyle(string $language = null): StyleInterface;
}
