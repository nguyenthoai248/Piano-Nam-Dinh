<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email;

use WC_Abstract_Order;
use WP_Error;
/**
 * Prepares raw report data for email delivery.
 */
interface SystemReportFormatterInterface
{
    /**
     * Format the system status report into HTML.
     *
     * @param string $rawSystemStatus The raw system status information.
     *
     * @return string Formatted HTML.
     */
    public function formatSystemStatus(string $rawSystemStatus): string;
    /**
     * Format a single order into HTML report.
     *
     * @param string $orderId The order ID.
     * @param WC_Abstract_Order $orderData The order data.
     *
     * @return string HTML content for this order.
     */
    public function formatOrder(string $orderId, WC_Abstract_Order $orderData): string;
    /**
     * Format error information about an order.
     *
     * @param string $orderId The order ID.
     * @param WP_Error $error The error information.
     *
     * @return string HTML content.
     */
    public function formatOrderError(string $orderId, WP_Error $error): string;
    /**
     * Format log content for email delivery.
     *
     * @param string $logContent Raw log content.
     *
     * @return string Formatted log content.
     */
    public function formatLogContent(string $logContent): string;
}
