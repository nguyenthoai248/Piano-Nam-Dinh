<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style;

/**
 * A service able to convert Style instance into array.
 */
interface StyleSerializerInterface
{
    /**
     * Convert Style object into an array.
     *
     * @param StyleInterface $style Style object to serialize.
     *
     * @return array{language?: string, hostedVersion?: string} Array containing style data.
     */
    public function serializeStyle(StyleInterface $style): array;
}
