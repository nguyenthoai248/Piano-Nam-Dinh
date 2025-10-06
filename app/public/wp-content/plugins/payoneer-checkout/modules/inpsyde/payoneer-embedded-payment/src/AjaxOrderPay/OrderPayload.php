<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\AjaxOrderPay;

use WP_Rewrite;
/**
 * Pun intended
 */
class OrderPayload
{
    /**
     * @var \WC_Order
     */
    protected $order;
    /**
     * @var \WC_Customer
     */
    protected $customer;
    /**
     * @var array
     */
    protected $formData;
    public function __construct(\WC_Order $order, \WC_Customer $customer, array $formData)
    {
        $this->order = $order;
        $this->customer = $customer;
        $this->formData = $formData;
    }
    public static function fromGlobals(): self
    {
        $fields = filter_input(\INPUT_POST, 'fields', \FILTER_CALLBACK, ['options' => 'strip_tags']);
        if (!is_string($fields)) {
            throw new \OutOfBoundsException('"fields" key not found in POST data');
        }
        parse_str($fields, $formData);
        $nonceValue = (string) wc_get_var($formData['woocommerce-pay-nonce'], (string) wc_get_var($formData['_wpnonce'], ''));
        if (!wp_verify_nonce($nonceValue, 'woocommerce-pay')) {
            throw new \DomainException('Failed nonce validation');
        }
        if (!isset($formData['_wp_http_referer'])) {
            throw new \InvalidArgumentException('Missing referer URL');
        }
        $referer = (string) $formData['_wp_http_referer'];
        $orderId = self::generateOrderIdFromReferer($referer);
        $orderKey = self::generateOrderKeyFromReferer($referer);
        $order = wc_get_order($orderId);
        if ($order instanceof \WC_Order && $order->get_id() > 0 && hash_equals($order->get_order_key(), $orderKey) && $order->needs_payment()) {
            return new self($order, WC()->customer, $formData);
        }
        throw new \RuntimeException('Could not validate payment request');
    }
    private static function generateOrderIdFromReferer(string $referer): int
    {
        $parsedUrl = wp_parse_url($referer);
        if (!is_array($parsedUrl)) {
            throw new \InvalidArgumentException('Failed to parse referer');
        }
        $queryParams = [];
        wp_parse_str((string) $parsedUrl['query'], $queryParams);
        global $wp_rewrite;
        assert($wp_rewrite instanceof WP_Rewrite);
        if (!$wp_rewrite->using_permalinks()) {
            return (int) $queryParams['order-pay'];
        }
        $path = explode('/', untrailingslashit((string) $parsedUrl['path']));
        return (int) end($path);
    }
    private static function generateOrderKeyFromReferer(string $referer): string
    {
        $parsedUrl = wp_parse_url($referer);
        if (!is_array($parsedUrl)) {
            throw new \InvalidArgumentException('Failed to parse referer');
        }
        $queryParams = [];
        wp_parse_str((string) $parsedUrl['query'], $queryParams);
        return (string) $queryParams['key'];
    }
    /**
     * @return \WC_Order
     */
    public function getOrder(): \WC_Order
    {
        return $this->order;
    }
    /**
     * @return \WC_Customer
     */
    public function getCustomer(): \WC_Customer
    {
        return $this->customer;
    }
    /**
     * @return array
     */
    public function getFormData(): array
    {
        return $this->formData;
    }
}
