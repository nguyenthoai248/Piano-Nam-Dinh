<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector;

use Exception;
use Automattic\WooCommerce\Internal\Admin\Logging\FileV2\FileController;
use Automattic\WooCommerce\Internal\Admin\Logging\FileV2\File;
/**
 * Collects logs from file-based logging system.
 *
 * Works with the newer WooCommerce FileController V2 system.
 *
 * Note that the `FileController` class implements a log file rotation, once the file reaches a
 * certain size - the default is 5MB. When this happens this collector will return the contents of
 * the last log file (the latest one).
 */
class FileLogCollector implements LogCollectorInterface
{
    /**
     * @var string
     */
    private string $loggingSource;
    /**
     * @var FileController|null FileController instance if available
     */
    private ?FileController $fileController;
    /**
     * @param string $loggingSource The logging source identifier.
     * @param FileController|null $fileController WooCommerce file-logging controller.
     */
    public function __construct(string $loggingSource, ?FileController $fileController = null)
    {
        $this->loggingSource = $loggingSource;
        $this->fileController = $fileController ?? $this->resolveFileController();
    }
    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'File system (V2)';
    }
    /**
     * @inheritDoc
     */
    public function collectForDate(string $date, ?int $maxSizeBytes = null): string
    {
        if (!$this->fileController) {
            return '';
        }
        try {
            /**
             * Apply the same sanitization as the WooCommerce file-logging controller.
             *
             * @see LogHandlerFileV2::handle()
             */
            $source = sanitize_title(trim($this->loggingSource));
            $startTimestamp = strtotime($date . ' 00:00:00');
            $endTimestamp = strtotime($date . ' 23:59:59');
            if ($startTimestamp === \false || $endTimestamp === \false) {
                return '';
            }
            // Get log files for the specified source and date range.
            $files = $this->fileController->get_files(['source' => $source, 'date_filter' => 'created', 'date_start' => $startTimestamp, 'date_end' => $endTimestamp, 'order' => 'desc']);
            if (empty($files) || is_wp_error($files)) {
                return '';
            }
            return $this->collectFileContent($files, $maxSizeBytes);
        } catch (Exception $exception) {
            return "Error reading log files: " . $exception->getMessage();
        }
    }
    /**
     * Try to resolve the FileController from WooCommerce container.
     *
     * @return FileController|null FileController if available, null otherwise
     */
    private function resolveFileController(): ?FileController
    {
        try {
            /** @var FileController|null $controller */
            $controller = wc_get_container()->get(FileController::class);
            return $controller;
        } catch (Exception $exception) {
            return null;
        }
    }
    /**
     * Collect content from files, respecting the optional size limit.
     *
     * @param File[] $files Array of log files.
     * @param int|null $maxSizeBytes Maximum size in bytes or null for no limit.
     *
     * @return string Collected log content.
     */
    private function collectFileContent(array $files, ?int $maxSizeBytes = null): string
    {
        $logContent = [];
        $totalSize = 0;
        $filesIncluded = 0;
        $truncated = \false;
        foreach ($files as $file) {
            $result = $this->processFile($file, $totalSize, $maxSizeBytes);
            if ($result === null) {
                continue;
            }
            $filesIncluded++;
            $totalSize += $result['size'];
            array_unshift($logContent, $result['content']);
            if ($result['stopProcessing']) {
                $truncated = $result['truncated'];
                break;
            }
        }
        if ($truncated) {
            array_unshift($logContent, $this->formatTruncationMessage($filesIncluded, count($files), $maxSizeBytes));
        }
        return implode("\n\n", $logContent);
    }
    /**
     * Process a single file and return its content with metadata.
     *
     * @return array{content: string, size: int, truncated: bool, stopProcessing: bool}|null
     */
    private function processFile(File $file, int $currentTotalSize, ?int $maxSizeBytes): ?array
    {
        $filePath = $file->get_path();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }
        $fileSize = filesize($filePath);
        if ($fileSize === \false) {
            return null;
        }
        $fileName = str_replace($file->get_hash(), '#####', basename($filePath));
        $fileHeader = "=== Content from {$fileName} ===\n\n";
        // Check if we can include the full file
        if ($maxSizeBytes === null || $currentTotalSize + $fileSize <= $maxSizeBytes) {
            $content = (string) @file_get_contents($filePath);
            return ['content' => $fileHeader . $content, 'size' => $fileSize, 'truncated' => \false, 'stopProcessing' => \false];
        }
        // Handle size limit exceeded
        return $this->readPartialFile($filePath, $fileHeader, $currentTotalSize, $maxSizeBytes);
    }
    /**
     * Read partial content from a file when size limit is reached.
     *
     * @return array{content: string, size: int, truncated: bool, stopProcessing: bool}
     */
    private function readPartialFile(string $filePath, string $fileHeader, int $currentTotalSize, int $maxSizeBytes): array
    {
        $remainingBytes = $maxSizeBytes - $currentTotalSize;
        if ($remainingBytes < 1) {
            return ['content' => $fileHeader . '--- Skipped, because size limit reached. ---', 'size' => 0, 'truncated' => \false, 'stopProcessing' => \true];
        }
        $fileSize = (int) filesize($filePath);
        $seekPosition = min($remainingBytes, $fileSize);
        // Read only the last portion of the file
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return ['content' => $fileHeader . '--- Skipped, file cannot be opened. ----', 'size' => 0, 'truncated' => \false, 'stopProcessing' => \false];
        }
        fseek($handle, -$seekPosition, \SEEK_END);
        $partialContent = fread($handle, $remainingBytes);
        fclose($handle);
        if (\false === $partialContent) {
            return ['content' => $fileHeader . '--- Skipped, file cannot be read. ---', 'size' => 0, 'truncated' => \false, 'stopProcessing' => \false];
        }
        return ['content' => $fileHeader . "--- This file is truncated. ---\n" . $partialContent, 'size' => strlen($partialContent), 'truncated' => \true, 'stopProcessing' => \true];
    }
    /**
     * Format the truncation message for the log output.
     */
    private function formatTruncationMessage(int $filesIncluded, int $totalFiles, ?int $maxSizeBytes): string
    {
        return sprintf("--- Content includes last %d of %d files, limiting result to %s logs. ---", $filesIncluded, $totalFiles, $maxSizeBytes !== null ? size_format($maxSizeBytes, 2) : 'all');
    }
}
