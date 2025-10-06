<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\AjaxOrderPay;

use Exception;
use WC_Order;
class AjaxPayAction
{
    /**
     * @var string[]
     */
    protected $payoneerGatewayIds;
    /**
     * @param string[] $payoneerGatewayIds
     */
    public function __construct(array $payoneerGatewayIds)
    {
        $this->payoneerGatewayIds = $payoneerGatewayIds;
    }
    /**
     * phpcs:disable WordPress.Security.NonceVerification.Missing
     * phpcs:disable Inpsyde.CodeQuality.NoElse.ElseFound
     * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
     * @see \WC_Form_Handler::pay_action()
     * @param \WC_Order $order
     * @param \WC_Customer $customer
     * @param array $data form POST data
     *
     * @return bool
     */
    public function __invoke(\WC_Order $order, \WC_Customer $customer, array $data): bool
    {
        do_action('woocommerce_before_pay_action', $order);
        $customer->set_props(['billing_country' => $order->get_billing_country() ? $order->get_billing_country() : null, 'billing_state' => $order->get_billing_state() ? $order->get_billing_state() : null, 'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null, 'billing_city' => $order->get_billing_city() ? $order->get_billing_city() : null]);
        $customer->save();
        if (!empty($data['terms-field']) && empty($data['terms'])) {
            wc_add_notice(__('Please read and accept the terms and conditions to proceed with your order.', 'woocommerce'), 'error');
            return \false;
        }
        // Update payment method.
        if ($order->needs_payment()) {
            try {
                $paymentGateway = $this->getUsedPaymentGateway($data);
                if (!$this->isPayoneerPaymentMethod($paymentGateway)) {
                    throw new Exception(__('Invalid payment method.', 'woocommerce'));
                }
                $this->updateOrderPaymentMethodData($order, $paymentGateway);
                if (0 === wc_notice_count('error')) {
                    $orderId = $order->get_id();
                    $result = $paymentGateway->process_payment($orderId);
                    // Redirect to success/confirmation/payment page.
                    if (isset($result['result']) && 'success' === $result['result']) {
                        return \true;
                    }
                }
            } catch (\Exception $exception) {
                wc_add_notice($exception->getMessage(), 'error');
                return \false;
            }
        } else {
            // No payment was required for order.
            $order->payment_complete();
            return \true;
        }
        do_action('woocommerce_after_pay_action', $order);
        return \false;
    }
    /**
     * @param WC_Order $order
     * @param \WC_Payment_Gateway $paymentGateway
     * @return void
     * @throws \WC_Data_Exception
     */
    protected function updateOrderPaymentMethodData(WC_Order $order, \WC_Payment_Gateway $paymentGateway): void
    {
        $order->set_payment_method($paymentGateway->id);
        $order->set_payment_method_title($paymentGateway->get_title());
        $order->save();
    }
    /**
     * Get used payment gateway
     *
     * @param array<array-key, mixed> $postedData
     *
     * @return \WC_Payment_Gateway
     *
     * @throws Exception
     */
    protected function getUsedPaymentGateway(array $postedData): \WC_Payment_Gateway
    {
        $paymentMethod = wp_unslash($postedData['payment_method'] ?? '');
        assert(is_string($paymentMethod));
        $paymentMethodId = $paymentMethod ? wc_clean($paymentMethod) : \false;
        $availableGateways = WC()->payment_gateways()->get_available_payment_gateways();
        $paymentGateway = $availableGateways[$paymentMethodId] ?? \false;
        if (!$paymentGateway instanceof \WC_Payment_Gateway) {
            throw new Exception(__('Invalid payment method.', 'woocommerce'));
        }
        return $paymentGateway;
    }
    /**
     * Detect whether used payment method is from this plugin.
     *
     * @param \WC_Payment_Gateway $gateway
     * @return bool
     */
    public function isPayoneerPaymentMethod(\WC_Payment_Gateway $gateway): bool
    {
        return in_array($gateway->id, $this->payoneerGatewayIds, \true);
    }
}
