<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\Callback;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\SecurityHeader\SecurityHeaderFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use WC_Order;
class WcOrderBasedCallbackFactory implements WcOrderBasedCallbackFactoryInterface
{
    /**
     * @var CallbackFactoryInterface
     */
    protected $callbackFactory;
    /**
     * @var UriInterface
     */
    protected $notificationUrl;
    /**
     * @var SecurityHeaderFactoryInterface
     */
    protected $securityHeaderFactory;
    /**
     * @var string
     */
    protected $securityHeaderFieldName;
    /**
     * @param CallbackFactoryInterface $callbackFactory
     * @param UriInterface $notificationUrl
     * @param SecurityHeaderFactoryInterface $securityHeaderFactory
     * @param string $securityHeaderFieldName
     */
    public function __construct(CallbackFactoryInterface $callbackFactory, UriInterface $notificationUrl, SecurityHeaderFactoryInterface $securityHeaderFactory, string $securityHeaderFieldName)
    {
        $this->callbackFactory = $callbackFactory;
        $this->notificationUrl = $notificationUrl;
        $this->securityHeaderFactory = $securityHeaderFactory;
        $this->securityHeaderFieldName = $securityHeaderFieldName;
    }
    /**
     * @inheritDoc
     */
    public function createCallback(WC_Order $order) : CallbackInterface
    {
        $token = (string) $order->get_meta($this->securityHeaderFieldName, \true);
        $header = $this->securityHeaderFactory->createSecurityHeader($token);
        return $this->callbackFactory->createCallback($order->get_checkout_order_received_url(), $order->get_view_order_url(), $order->get_checkout_payment_url(), (string) $this->notificationUrl, [$header]);
    }
}
