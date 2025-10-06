<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

use InvalidArgumentException;
/**
 * Service able to convert an array with style data into a Style object.
 */
interface StyleDeserializerInterface
{
    /**
     * Convert array with Style data to a Style object.
     *
     * @param array{language?: string} $styleData Data for Style.
     *
     * @return StyleInterface Created object.
     *
     * @throws InvalidArgumentException If style data contains no expected element.
     */
    public function deserializeStyle(array $styleData): StyleInterface;
}
