<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Identification;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Represents a collection of different parameters to identify this operation
 * supplied by merchant, optile and PSP.
 */
interface IdentificationInterface
{
    /**
     * Return session longId.
     *
     * @return string Globally unique operation identifier assigned by OPG platform.
     */
    public function getLongId(): string;
    /**
     * Return session shortId.
     *
     * @return string Short identifier assigned by OPG platform to operation, not guaranteed to be unique.
     */
    public function getShortId(): string;
    /**
     * Return transaction id.
     *
     * @return string Transaction id initially provided by merchant.
     */
    public function getTransactionId(): string;
    /**
     * Return PSP id.
     *
     * @return string Transaction id assigned by PSP, may be not set.
     *
     * @throws ApiExceptionInterface If this field is not set.
     */
    public function getPspId(): string;
}
