<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use WC_Cart;
use WC_Customer;
use WooCommerce;
class CheckoutHashProvider implements HashProviderInterface
{
    /**
     * @var WooCommerce
     */
    protected $wooCommerce;
    /**
     * @param WooCommerce $wooCommerce
     */
    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }
    /**
     * @inheritDoc
     */
    public function provideHash(): string
    {
        $dataToHash = $this->provideDataToHash();
        return md5(serialize($dataToHash));
    }
    /**
     * @return array
     *
     * @throws CheckoutException
     */
    protected function provideDataToHash(): array
    {
        $cart = $this->wooCommerce->cart;
        if (!$cart instanceof WC_Cart) {
            throw new CheckoutException('Failed to prepare checkout hash, cart must be initialized first.');
        }
        $customer = $this->wooCommerce->customer;
        if (!$customer instanceof WC_Customer) {
            throw new CheckoutException('Failed to prepare checkout hash, customer must be initialized first.');
        }
        /**
         * We have to retrieve a cart, a country and a currency in the runtime
         * to have actual information.
         */
        return [$cart->get_total('edit'), get_woocommerce_currency(), $customer->get_billing_country(), $customer->get_shipping_country()];
    }
}
