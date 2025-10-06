<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector;

use WC_Log_Handler_File;
/**
 * Collects logs from the legacy file-based logging system.
 *
 * Works with the older WC_Log_Handler_File which stores logs in dated files.
 */
class LegacyFileLogCollector implements LogCollectorInterface
{
    /**
     * @var string
     */
    private string $loggingSource;
    /**
     * @param string $loggingSource The logging source identifier
     */
    public function __construct(string $loggingSource)
    {
        $this->loggingSource = $loggingSource;
    }
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'File system (legacy)';
    }
    /**
     * @inheritDoc
     */
    public function collectForDate(string $date, ?int $maxSizeBytes = null): string
    {
        if (!class_exists('WC_Log_Handler_File')) {
            return '';
        }
        $dateSuffix = '-' . $date;
        $logFilename = $this->loggingSource . $dateSuffix;
        $logPath = WC_Log_Handler_File::get_log_file_path($logFilename);
        if (!is_string($logPath) || !file_exists($logPath) || !is_readable($logPath)) {
            return '';
        }
        // If no size limit or file is smaller than limit, return entire content.
        $fileSize = filesize($logPath);
        if ($fileSize === \false) {
            return '';
        }
        if ($maxSizeBytes === null || $fileSize <= $maxSizeBytes) {
            $content = @file_get_contents($logPath);
            return $content !== \false ? $content : '';
        }
        // If file is larger than limit, read only the last portion.
        $handle = @fopen($logPath, 'rb');
        if ($handle === \false) {
            return '';
        }
        fseek($handle, -$maxSizeBytes, \SEEK_END);
        $content = fread($handle, $maxSizeBytes);
        fclose($handle);
        if ($content === \false) {
            return '--- Log file could not be read. ---';
        }
        // Add a note to describe the truncation.
        $truncationNote = sprintf("--- Log file was truncated. Showing last %s of %s total. ---\n\n", size_format($maxSizeBytes, 2), size_format($fileSize, 2));
        return $truncationNote . $content;
    }
}
