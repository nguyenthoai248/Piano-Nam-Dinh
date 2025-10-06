<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderSecurityValidator;

/**
 * Service able to check token for order.
 */
interface OrderSecurityValidatorInterface
{
    /**
     * Check if token for the order valid.
     *
     * @param \WC_Order $order Order to check token for.
     * @param string $token The token to check.
     *
     * @return bool
     */
    public function orderTokenValid(\WC_Order $order, string $token) : bool;
}
