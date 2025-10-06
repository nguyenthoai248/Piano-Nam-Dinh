<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\RequestHeaderUtil;
/**
 * Describes an "order-based" context.
 * Usually, this applies to payments to be done on the pay-for-order page.
 * However, since LISTs are transferred to orders during checkout, you will also see this context
 * in use during regular checkout.
 */
class PaymentContext extends AbstractContext
{
    private ?\WC_Order $order;
    public function __construct(?\WC_Order $order = null)
    {
        $this->order = $order;
    }
    public function getCart(): ?\WC_Cart
    {
        return WC()->cart;
    }
    public function getCustomer(): ?\WC_Customer
    {
        return WC()->customer;
    }
    public function getSession(): ?\WC_Session
    {
        return WC()->session;
    }
    public function getOrder(): ?\WC_Order
    {
        if ($this->order) {
            return $this->order;
        }
        /**
         * When using the Store API we currently rely on custom HHT header to pass information
         * about the current checkout attempt.
         */
        $headerUtil = new RequestHeaderUtil();
        $paymentCheckoutHeader = 'x-payoneer-is-payment-checkout';
        $orderKey = $headerUtil->getHeader($paymentCheckoutHeader);
        if (!empty($orderKey)) {
            $orderId = wc_get_order_id_by_order_key($orderKey);
            $order = wc_get_order($orderId);
            if ($order instanceof \WC_Order) {
                return $order;
            }
        }
        /**
         * For initial page loads, we can use classic WP/WC functions to inspect the current request
         */
        if (is_checkout_pay_page() || isset($_POST['action']) && $_POST['action'] === 'payoneer_order_pay') {
            $orderId = get_query_var('order-pay');
            $order = wc_get_order((int) $orderId);
            // Ensure the order is of type WC_Order
            if ($order instanceof \WC_Order) {
                return $order;
            }
        }
        return null;
    }
}
