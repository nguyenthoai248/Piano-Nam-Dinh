<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\PaymentProcessor;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use WC_Order;
class MetadataSavingProcessorDecorator implements PaymentProcessorInterface
{
    /**
     * @var PaymentProcessorInterface
     */
    protected $inner;
    /**
     * @var MerchantInterface
     */
    protected $merchant;
    /**
     * @var string
     */
    protected $merchantIdFieldName;
    /**
     * @var string
     */
    protected string $transactionUrlTemplateFieldName;
    public function __construct(PaymentProcessorInterface $inner, MerchantInterface $merchant, string $merchantIdFieldName, string $transactionUrlTemplateFieldName)
    {
        $this->inner = $inner;
        $this->merchant = $merchant;
        $this->merchantIdFieldName = $merchantIdFieldName;
        $this->transactionUrlTemplateFieldName = $transactionUrlTemplateFieldName;
    }
    public function processPayment(WC_Order $order, PaymentGateway $gateway): array
    {
        $this->addMetaDataToOrder($order);
        return $this->inner->processPayment($order, $gateway);
    }
    /**
     * Add meta fields to order.
     *
     * @param WC_Order $order Order to add meta fields to.
     */
    protected function addMetaDataToOrder(WC_Order $order): void
    {
        /**
         * Store Merchant ID
         */
        $merchantId = $this->merchant->getId();
        $order->update_meta_data($this->merchantIdFieldName, (string) $merchantId);
        /**
         * Store transaction ID
         */
        $transactionUrlTemplate = $this->merchant->getTransactionUrlTemplate();
        $order->update_meta_data($this->transactionUrlTemplateFieldName, $transactionUrlTemplate);
        $order->save();
    }
}
