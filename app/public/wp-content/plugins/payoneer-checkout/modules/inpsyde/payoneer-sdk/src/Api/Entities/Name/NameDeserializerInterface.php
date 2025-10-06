<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

/**
 * Service able to convert array to a Name instance.
 */
interface NameDeserializerInterface
{
    /**
     * Create a new Name object from array.
     *
     * @param array{firstName: string, lastName: string} $nameData Data to create Name instance.
     *
     * @return NameInterface Deserialized Name instance.
     */
    public function deserializeName(array $nameData): NameInterface;
}
