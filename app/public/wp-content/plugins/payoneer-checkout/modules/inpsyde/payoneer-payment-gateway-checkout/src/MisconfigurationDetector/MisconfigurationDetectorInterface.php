<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector;

use Throwable;
/**
 * A service able to detect by exception whether it caused by incorrect configuration provided
 * by merchant.
 */
interface MisconfigurationDetectorInterface
{
    /**
     * Check if given throwable was caused by incorrect configuration provided by merchant.
     *
     * @param Throwable $throwable
     *
     * @return bool
     */
    public function isCausedByMisconfiguration(Throwable $throwable): bool;
}
