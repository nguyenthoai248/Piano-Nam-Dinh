<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderWebhookFinder;

class OrderWebhookFinder implements OrderWebhookFinderInterface
{
    /**
     * @var string
     */
    protected $webhooksReceivedFieldName;
    /**
     * @param string $webhooksReceivedFieldName
     */
    public function __construct(string $webhooksReceivedFieldName)
    {
        $this->webhooksReceivedFieldName = $webhooksReceivedFieldName;
    }
    /**
     * @inheritDoc
     */
    public function hasRecord(\WC_Order $order, string $noticeId): bool
    {
        $processedWebhooks = $order->get_meta($this->webhooksReceivedFieldName);
        if (!is_array($processedWebhooks)) {
            return \false;
        }
        if (in_array($noticeId, $processedWebhooks, \true)) {
            return \true;
        }
        return \false;
    }
}
