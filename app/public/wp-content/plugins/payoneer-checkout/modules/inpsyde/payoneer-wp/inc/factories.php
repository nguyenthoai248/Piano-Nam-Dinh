<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return static fn() => ['wc.is_checkout' => new Factory(['wc'], static function (): bool {
    $currentPageId = \get_the_ID();
    /**
     * Our custom order_pay logic somehow isn't detected by WooCommerce as a checkout
     * request. I tracked it down to the place where our payment action request is not
     * detected as a 'page' in WP_Query while the native one is.
     *
     * We might fix it properly later, but currently we cannot invest much time into it.
     *
     * @todo: make our order_pay request be detected as a checkout in the same way as
     *      a native one.
     */
    //phpcs:disable WordPress.Security.NonceVerification.Missing
    return is_checkout() || isset($_POST['action']) && $_POST['action'] === 'payoneer_order_pay' || \method_exists(\WC_Blocks_Utils::class, 'has_block_in_page') && $currentPageId && \WC_Blocks_Utils::has_block_in_page($currentPageId, 'woocommerce/checkout');
}), 'wp.is_rest_api_request' => new Factory(['wc'], static function (\WooCommerce $wooCommerce) {
    global $wp_rewrite;
    \assert($wp_rewrite instanceof \WP_Rewrite);
    if ($wp_rewrite->using_permalinks()) {
        return $wooCommerce->is_rest_api_request();
    }
    /**
     * We really really wish to access raw data here.
     * Wea re also doing only string comparisons and will not use the data
     * for processing. Hence:
     * phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
     * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
     */
    return \preg_match('/\/index\.php\?rest_route=/', isset($_SERVER['REQUEST_URI']) ? \urldecode($_SERVER['REQUEST_URI']) : '') === 1;
    /**
     * phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
     * phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
     */
}), 'wc.session.is-available' => new Factory(['wc', 'wp.is_admin', 'wp.is_ajax'], static function (\WooCommerce $wooCommerce, bool $isAdmin, bool $isAjax): bool {
    if ($isAdmin && !$isAjax) {
        return \false;
    }
    return $wooCommerce->session instanceof \WC_Session;
}), 'wc.cart.is-available' => new Factory(['wc'], static fn(\WooCommerce $wooCommerce) => $wooCommerce->cart instanceof \WC_Cart), 'wc.is_checkout_pay_page' => new Factory(['wc'], static function (): bool {
    return is_checkout_pay_page();
}), 'wc.is_order_received_page' => new Factory(['wc'], static function (): bool {
    return is_order_received_page();
})];
