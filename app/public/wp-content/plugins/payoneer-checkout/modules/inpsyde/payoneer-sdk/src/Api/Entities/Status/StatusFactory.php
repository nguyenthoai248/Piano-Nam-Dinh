<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

class StatusFactory implements StatusFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createStatus(string $code, string $reason): StatusInterface
    {
        return new Status($code, $reason);
    }
}
