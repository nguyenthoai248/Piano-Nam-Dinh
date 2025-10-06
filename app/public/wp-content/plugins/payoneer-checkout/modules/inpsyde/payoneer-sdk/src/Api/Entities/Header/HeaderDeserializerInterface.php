<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * A service able to convert array to Header instance.
 */
interface HeaderDeserializerInterface
{
    /**
     * Convert array to Header instance.
     *
     * @param array{name: string, value: string} $headerData Header data to deserialize.
     *
     * @return HeaderInterface Deserialized header.
     *
     * @throws ApiExceptionInterface If failed to deserialize Header.
     */
    public function deserializeHeader(array $headerData): HeaderInterface;
}
