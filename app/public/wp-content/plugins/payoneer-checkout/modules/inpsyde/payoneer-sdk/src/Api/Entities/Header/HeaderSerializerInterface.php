<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

/**
 * Service able to convert Header instance to an array.
 */
interface HeaderSerializerInterface
{
    /**
     * Convert Header instance to an array.
     *
     * @param HeaderInterface $header Header to serialize.
     *
     * @return array{name: string, value: string} Serialized header
     */
    public function serializeHeader(HeaderInterface $header): array;
}
