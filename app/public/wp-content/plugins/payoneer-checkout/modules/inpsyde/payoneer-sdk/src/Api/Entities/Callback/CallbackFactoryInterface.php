<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;
/**
 * Service able to create Callback instance.
 */
interface CallbackFactoryInterface
{
    /**
     * Create a new Callback instance.
     *
     * @param string $returnUrl URL to redirect customer after successful payment.
     * @param string $summaryUrl URL of the payment page with selected payment method.
     * @param string $cancelUrl URL to redirect customer after cancelled or permanently
     *                          failed payment.
     * @param string|null $notificationUrl URL to send asynchronous notifications about
     *                                payments (webhooks).
     * @param HeaderInterface[] $notificationHeaders Set of headers defined by merchant
     *                                                      to be added to the notification
     *                                                      requests.
     *
     * @return CallbackInterface Created instance.
     */
    public function createCallback(string $returnUrl, string $summaryUrl, string $cancelUrl, string $notificationUrl = null, array $notificationHeaders = []): CallbackInterface;
}
