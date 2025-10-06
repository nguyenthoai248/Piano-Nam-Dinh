<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;
class Callback implements CallbackInterface
{
    /**
     * @var string The URL where customer should be redirected after payment.
     */
    protected $returnUrl;
    /**
     * @var string URL of the shop with selected payment method.
     */
    protected $summaryUrl;
    /**
     * @var string The URL where customer should be redirected after cancelled payment.
     */
    protected $cancelUrl;
    /**
     * @var string|null The URL where asynchronous notifications from remote API can be sent
     *      (webhook URL).
     */
    protected $notificationUrl;
    /**
     * @var HeaderInterface[] Merchant-defined headers to be sent back with webhook calls.
     */
    protected $notificationHeaders;
    /**
     * @param string $returnUrl
     * @param string $summaryUrl
     * @param string $cancelUrl
     * @param string|null $notificationUrl
     * @param array $notificationHeaders
     */
    public function __construct(string $returnUrl, string $summaryUrl, string $cancelUrl, string $notificationUrl = null, array $notificationHeaders = [])
    {
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
        $this->summaryUrl = $summaryUrl;
        $this->notificationUrl = $notificationUrl;
        $this->notificationHeaders = $notificationHeaders;
    }
    /**
     * @inheritDoc
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
    /**
     * @inheritDoc
     */
    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }
    /**
     * @inheritDoc
     */
    public function getSummaryUrl(): string
    {
        return $this->summaryUrl;
    }
    /**
     * @inheritDoc
     */
    public function getNotificationUrl(): string
    {
        if ($this->notificationUrl === null) {
            throw new ApiException('notificationUrl field not set.');
        }
        return $this->notificationUrl;
    }
    /**
     * @inheritDoc
     */
    public function getNotificationHeaders(): array
    {
        return $this->notificationHeaders;
    }
}
