<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
/**
 * Service able to create a new instance of StatusInterface.
 */
interface StatusFactoryInterface
{
    /**
     * Create a new Status instance.
     *
     * @param string $code One of the valid statuses.
     * @param string $reason One of the valid reason phrases.
     *
     * @return StatusInterface Created object.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function createStatus(string $code, string $reason): StatusInterface;
}
