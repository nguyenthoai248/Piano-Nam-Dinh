<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector;

use stdClass;
use WC_Log_Levels;
use wpdb;
use RuntimeException;
/**
 * Collects logs from the database-based logging system.
 *
 * Works with WooCommerce DB logger, which stores logs in the woocommerce_log table.
 */
class DbLogCollector implements LogCollectorInterface
{
    /**
     * @var string
     */
    private string $loggingSource;
    /**
     * @var wpdb
     */
    private wpdb $wpdb;
    /**
     * @param string $loggingSource The logging source identifier.
     * @param wpdb|null $wpdb WordPress database object.
     */
    public function __construct(string $loggingSource, ?wpdb $wpdb = null)
    {
        $this->loggingSource = $loggingSource;
        if (!$wpdb) {
            if (!$GLOBALS['wpdb'] instanceof wpdb) {
                throw new RuntimeException('Global $wpdb object not defined');
            }
            $wpdb = $GLOBALS['wpdb'];
        }
        $this->wpdb = $wpdb;
    }
    /**
     * The DB table name, which contains the log data.
     *
     * @return string
     */
    private function tableName(): string
    {
        return $this->wpdb->prefix . 'woocommerce_log';
    }
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Database (woocommerce_log)';
    }
    /**
     * @inheritDoc
     */
    public function collectForDate(string $date, ?int $maxSizeBytes = null): string
    {
        // Verify if date has valid format (mainly for security).
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }
        $totalEntries = $this->countLogsForDate($date);
        if ($totalEntries === 0) {
            return '';
        }
        return $this->collectLogBatches($date, $totalEntries, $maxSizeBytes);
    }
    /**
     * Count log entries for a specific date.
     */
    private function countLogsForDate(string $date): int
    {
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        $tableName = $this->tableName();
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is static.
        $countQuery = (string) $this->wpdb->prepare("SELECT COUNT(*)\n             FROM `{$tableName}`\n             WHERE source LIKE %s AND timestamp BETWEEN %s AND %s", $this->wpdb->esc_like($this->loggingSource) . '%', $startDate, $endDate);
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $this->wpdb->get_var($countQuery);
    }
    /**
     * Collect log entries in batches.
     */
    private function collectLogBatches(string $date, int $totalEntries, ?int $maxSizeBytes): string
    {
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        $tableName = $this->tableName();
        $batchSize = 250;
        $includedEntries = 0;
        $logContents = [];
        $currentSize = 0;
        while ($includedEntries < $totalEntries) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is static.
            $query = (string) $this->wpdb->prepare("SELECT timestamp, level, message, source, context\n                FROM `{$tableName}`\n                WHERE source LIKE %s AND timestamp BETWEEN %s AND %s\n                ORDER BY timestamp DESC\n                LIMIT %d OFFSET %d", $this->wpdb->esc_like($this->loggingSource) . '%', $startDate, $endDate, $batchSize, $includedEntries);
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $logs = $this->wpdb->get_results($query);
            if (!is_array($logs) || empty($logs)) {
                break;
            }
            $batchResult = $this->processBatch($logs, $currentSize, $maxSizeBytes, $includedEntries, $totalEntries);
            $includedEntries += $batchResult['processed'];
            $currentSize = $batchResult['size'];
            array_unshift($logContents, $batchResult['content']);
            if ($batchResult['truncated']) {
                break;
            }
        }
        return implode("\n", $logContents);
    }
    /**
     * Process a single batch of log entries.
     *
     * @return array{processed: int, size: int, content: string, truncated: bool}
     */
    private function processBatch(array $logs, int $currentSize, ?int $maxSizeBytes, int $includedEntries, int $totalEntries): array
    {
        $batchContent = [];
        $processed = 0;
        $truncated = \false;
        foreach ($logs as $entry) {
            /** @var stdClass $entryObject */
            $entryObject = (object) $entry;
            $line = $this->formatLogEntry($entryObject);
            $currentSize += strlen($line);
            if ($maxSizeBytes !== null && $currentSize > $maxSizeBytes) {
                $truncated = \true;
                $batchContent[] = sprintf('--- Log data was truncated. Showing %d of %d log entries. ---', $includedEntries, $totalEntries);
                break;
            }
            $processed++;
            $batchContent[] = $line;
        }
        return ['processed' => $processed, 'size' => $currentSize, 'content' => implode("\n", array_reverse($batchContent)), 'truncated' => $truncated];
    }
    /**
     * Formats the DB results into a "log file" string.
     *
     * @param stdClass $entry A single item of the array returned by `wpdb::get_results()`.
     *
     * @return string
     */
    private function formatLogEntry(stdClass $entry): string
    {
        $timestamp = (string) $entry->timestamp;
        $level = (string) WC_Log_Levels::get_severity_level((int) $entry->level);
        $message = (string) $entry->message;
        return sprintf("%s\t[%s] %s", $timestamp, strtoupper($level), $message);
    }
}
