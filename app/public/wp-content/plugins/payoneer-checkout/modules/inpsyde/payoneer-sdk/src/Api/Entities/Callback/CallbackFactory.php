<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

class CallbackFactory implements CallbackFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCallback(string $returnUrl, string $summaryUrl, string $cancelUrl, string $notificationUrl = null, array $notificationHeaders = []): CallbackInterface
    {
        return new Callback($returnUrl, $summaryUrl, $cancelUrl, $notificationUrl, $notificationHeaders);
    }
}
