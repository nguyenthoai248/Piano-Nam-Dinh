<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector;

/**
 * Interface for log collection services.
 *
 * Defines the contract for collecting logs from various sources (file, database)
 * for a specific date.
 */
interface LogCollectorInterface
{
    /**
     * Name of the log collector, which defines the source of the log data.
     *
     * @return string
     */
    public function name(): string;
    /**
     * Collect logs for a specific date.
     *
     * @param string $date Date in Y-m-d format.
     * @param int|null $maxSizeBytes Maximum size in bytes to collect (null for no limit).
     *
     * @return string Raw log content.
     */
    public function collectForDate(string $date, ?int $maxSizeBytes = null): string;
}
