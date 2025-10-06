<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

/**
 * A service able to convert Name instance to an array.
 */
interface NameSerializerInterface
{
    /**
     * Convert Name instance to array.
     *
     * @return array{firstName: string, lastName: string} Serialized Name instance.
     */
    public function serializeName(NameInterface $name): array;
}
