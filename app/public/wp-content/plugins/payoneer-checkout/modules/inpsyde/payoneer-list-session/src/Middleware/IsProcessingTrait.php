<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

trait IsProcessingTrait
{
    /**
     * Detect if payment is processed in the current request.
     *
     * Use with caution, if called before payment processing started, may return
     * false-negative result.
     *
     * @return bool
     */
    protected function isProcessing(): bool
    {
        if (did_action('woocommerce_before_checkout_process') > 0 || did_action('woocommerce_rest_checkout_process_payment_with_context') > 0) {
            return \true;
        }
        /**
         * Now check specifically for PaymentContext
         */
        return did_action('woocommerce_before_pay_action') > 0;
    }
}
