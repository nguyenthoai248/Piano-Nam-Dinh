<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\AvailabilityCallback;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\AjaxOrderPay\OrderPayload;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\ListCondition\ListConditionInterface;
use WC_Payment_Gateway;
class ListConditionAvailabilityCallback implements AvailabilityCallbackInterface
{
    protected ListSessionManager $listSessionManager;
    protected ListConditionInterface $condition;
    protected bool $isAjaxOrderPay;
    public function __construct(ListSessionManager $listSessionManager, ListConditionInterface $condition, bool $isAjaxOrderPay)
    {
        $this->listSessionManager = $listSessionManager;
        $this->condition = $condition;
        $this->isAjaxOrderPay = $isAjaxOrderPay;
    }
    public function __invoke(WC_Payment_Gateway $gateway): bool
    {
        try {
            $order = null;
            if ($this->isAjaxOrderPay) {
                $orderPayload = OrderPayload::fromGlobals();
                $order = $orderPayload->getOrder();
            }
            $listSession = $this->listSessionManager->provide(new PaymentContext($order));
        } catch (\Throwable $exception) {
            return \false;
        }
        return $this->condition->valid($listSession);
    }
}
