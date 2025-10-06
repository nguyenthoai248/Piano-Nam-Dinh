<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

use InvalidArgumentException;
/**
 * A service able to convert array to a System object.
 */
interface SystemDeserializerInterface
{
    /**
     * Create a System instance from a data array.
     *
     * @param array $systemData
     *
     * @return SystemInterface
     *
     * @throws InvalidArgumentException If data contains no expected elements.
     */
    public function deserializeSystem(array $systemData): SystemInterface;
}
