<?php

declare (strict_types=1);
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter -- this is a DTO, which contains getters.
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoSetter -- this is a DTO, which contains setters.
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data;

use WC_Abstract_Order;
use WP_Error;
/**
 * Data structure for the collected system report data.
 */
class SystemReportDataDTO
{
    /**
     * @var string
     */
    private string $statusReport = '';
    /**
     * @var array<int|string, WP_Error|WC_Abstract_Order>
     */
    private array $ordersReport = [];
    /**
     * @var string
     */
    private string $logContent = '';
    /**
     * @var string
     */
    private string $logDate = '';
    /**
     * @var string
     */
    private string $logSource = '';
    /**
     * Get the raw WooCommerce system status report information.
     *
     * @return string Content of the WC status report, as HTML.
     */
    public function getStatusReport(): string
    {
        return $this->statusReport;
    }
    /**
     * Set the raw WooCommerce system status report information.
     *
     * @param string $statusReport Content of the WC status report, as HTML.
     *
     * @return SystemReportDataDTO
     */
    public function setStatusReport(string $statusReport): SystemReportDataDTO
    {
        $this->statusReport = $statusReport;
        return $this;
    }
    /**
     * Get the order data for the specified orders.
     *
     * @return array<int|string, WP_Error|WC_Abstract_Order> Key is the order ID,
     * value contains the collected order data.
     */
    public function getOrders(): array
    {
        return $this->ordersReport;
    }
    /**
     * Set the order data for the specified orders.
     *
     * @param array<int|string, WP_Error|WC_Abstract_Order> $ordersReport Key is the order ID,
     * value contains the collected order data.
     *
     * @return SystemReportDataDTO
     */
    public function setOrders(array $ordersReport): SystemReportDataDTO
    {
        $this->ordersReport = $ordersReport;
        return $this;
    }
    /**
     * Get the raw log content for the specified date.
     *
     * @return string Log content
     */
    public function getLogContent(): string
    {
        return $this->logContent;
    }
    /**
     * Set the raw log content for the specified date.
     *
     * @param string $logContent Log content
     *
     * @return SystemReportDataDTO
     */
    public function setLogContent(string $logContent): SystemReportDataDTO
    {
        $this->logContent = $logContent;
        return $this;
    }
    /**
     * Get the date for which logs were collected.
     *
     * @return string Date in Y-m-d format or empty if no logs.
     */
    public function getLogDate(): string
    {
        return $this->logDate;
    }
    /**
     * Set the date for which logs were collected.
     *
     * @param string $logDate Date in Y-m-d format or empty if no logs.
     *
     * @return SystemReportDataDTO
     */
    public function setLogDate(string $logDate): SystemReportDataDTO
    {
        $this->logDate = $logDate;
        return $this;
    }
    /**
     * Get the source of log data.
     *
     * @return string Which LogCollector implementation was used.
     */
    public function getLogSource(): string
    {
        return $this->logSource;
    }
    /**
     * Set the source of log data. Only a descriptive name that is displayed in the email.
     *
     * @param string $logSource Which LogCollector implementation was used.
     *
     * @return SystemReportDataDTO
     */
    public function setLogSource(string $logSource): SystemReportDataDTO
    {
        $this->logSource = $logSource;
        return $this;
    }
}
