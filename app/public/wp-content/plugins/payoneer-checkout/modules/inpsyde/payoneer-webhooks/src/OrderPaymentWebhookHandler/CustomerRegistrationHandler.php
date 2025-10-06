<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler;

use WC_Customer;
use WC_Order;
use WP_REST_Request;
class CustomerRegistrationHandler implements OrderPaymentWebhookHandlerInterface
{
    /**
     * @var string
     */
    protected $registrationIdFieldName;
    /**
     * @param string $registrationIdFieldName
     */
    public function __construct(string $registrationIdFieldName)
    {
        $this->registrationIdFieldName = $registrationIdFieldName;
    }
    /**
     * @inheritDoc
     */
    public function accepts(WP_REST_Request $request, WC_Order $order): bool
    {
        return $request->has_param('customerRegistrationId');
    }
    /**
     * @inheritDoc
     */
    public function handlePayment(WP_REST_Request $request, WC_Order $order): void
    {
        $customerId = $order->get_customer_id();
        $customer = new WC_Customer($customerId);
        $registrationId = (string) $request->get_param('customerRegistrationId');
        $customer->update_meta_data($this->registrationIdFieldName, $registrationId);
        $customer->save();
    }
}
