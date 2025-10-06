<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

/**
 * A service able to convert System object to array.
 */
interface SystemSerializerInterface
{
    /**
     * Convert System instance to an array.
     *
     * @param SystemInterface $system
     *
     * @return array{type: string, code: string}
     */
    public function serializeSystem(SystemInterface $system): array;
}
