<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

class StyleFactory implements StyleFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createStyle(string $language = null): StyleInterface
    {
        return new Style($language);
    }
}
