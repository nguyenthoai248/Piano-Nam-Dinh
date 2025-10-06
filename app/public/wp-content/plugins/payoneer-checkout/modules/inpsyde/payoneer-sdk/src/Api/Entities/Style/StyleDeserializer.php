<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

use InvalidArgumentException;
class StyleDeserializer implements StyleDeserializerInterface
{
    /**
     * @var StyleFactoryInterface
     */
    protected $styleFactory;
    /**
     * @param StyleFactoryInterface $styleFactory A factory to create a style instance.
     */
    public function __construct(StyleFactoryInterface $styleFactory)
    {
        $this->styleFactory = $styleFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeStyle(array $styleData): StyleInterface
    {
        $style = $this->styleFactory->createStyle($styleData['language'] ?? null);
        return $style;
    }
}
