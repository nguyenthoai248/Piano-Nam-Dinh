<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics;

use RuntimeException;
interface AnalyticsEventHandlerInterface
{
    /**
     * React to happened analytics event.
     *
     * @param array<string, mixed> $trackedHookConfig
     * @param array<string, string> $context
     *
     * @throws RuntimeException
     */
    public function handleAnalyticsEvent(array $trackedHookConfig, array $context): void;
}
