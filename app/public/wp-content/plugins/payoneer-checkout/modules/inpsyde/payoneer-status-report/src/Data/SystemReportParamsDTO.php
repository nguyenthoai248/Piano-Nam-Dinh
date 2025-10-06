<?php

declare (strict_types=1);
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter -- this is a DTO, which contains getters.
// phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoSetter -- this is a DTO, which contains setters.
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data;

/**
 * Defines the parameters (and their defaults) for generating a system report.
 *
 * This DTO is used for internal data transfer and documentation.
 */
class SystemReportParamsDTO
{
    /**
     * Default maximum log size in bytes (3MB).
     */
    private const DEFAULT_MAX_LOG_SIZE = 3 * 1024 * 1024;
    /**
     * List of order IDs or transaction IDs to include in the report.
     *
     * @var string[]
     */
    private array $orderIds = [];
    /**
     * Which log file to include in the report. Must be in Y-m-d format.
     * When empty or invalid, no log file is included.
     *
     * @var string
     */
    private string $logDate = '';
    /**
     * Maximum size of log content to include in bytes.
     *
     * @var int
     */
    private int $maxLogSize = self::DEFAULT_MAX_LOG_SIZE;
    /**
     * Get the list of order IDs or transaction IDs to include in the report.
     *
     * @return string[]
     */
    public function getOrderIds(): array
    {
        return $this->orderIds;
    }
    /**
     * Set the list of order IDs or transaction IDs to include in the report.
     *
     * @param string[] $orderIds
     *
     * @return SystemReportParamsDTO
     */
    public function setOrderIds(array $orderIds): SystemReportParamsDTO
    {
        $this->orderIds = $orderIds;
        return $this;
    }
    /**
     * Get the log date to include in the report.
     *
     * @return string
     */
    public function getLogDate(): string
    {
        return $this->logDate;
    }
    /**
     * Set the log date to include in the report.
     *
     * @param string $logDate
     *
     * @return SystemReportParamsDTO
     */
    public function setLogDate(string $logDate): SystemReportParamsDTO
    {
        $this->logDate = $logDate;
        return $this;
    }
    /**
     * Get the maximum size of log content to include in bytes.
     *
     * @return int Maximum size in bytes
     */
    public function getMaxLogSize(): int
    {
        return $this->maxLogSize;
    }
    /**
     * Set the maximum size of log content to include in bytes.
     *
     * @param int $maxLogSize Maximum size in bytes. Set to 0 to apply the default limit (3MB).
     *
     * @return SystemReportParamsDTO
     */
    public function setMaxLogSize(int $maxLogSize): SystemReportParamsDTO
    {
        $this->maxLogSize = $maxLogSize > 0 ? $maxLogSize : self::DEFAULT_MAX_LOG_SIZE;
        return $this;
    }
}
