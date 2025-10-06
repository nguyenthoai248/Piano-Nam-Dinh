<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector;

use Exception;
use WC_Log_Handler_DB;
use Automattic\WooCommerce\Internal\Admin\Logging\LogHandlerFileV2;
use Automattic\WooCommerce\Internal\Admin\Logging\Settings;
/**
 * Factory for creating the appropriate log collector based on WooCommerce's configuration.
 */
class LogCollectorFactory
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
     * Create the appropriate log collector based on WooCommerce's configuration.
     *
     * @return LogCollectorInterface
     */
    public function collector(): LogCollectorInterface
    {
        $handler = $this->getDefaultHandler();
        switch ($handler) {
            case LogHandlerFileV2::class:
                return new FileLogCollector($this->loggingSource);
            case WC_Log_Handler_DB::class:
                return new DbLogCollector($this->loggingSource);
        }
        // Default to the legacy filesystem handler.
        return new LegacyFileLogCollector($this->loggingSource);
    }
    /**
     * Get the default log handler class from WooCommerce settings.
     *
     * @return string Class name of the handler.
     */
    private function getDefaultHandler(): string
    {
        try {
            $settings = wc_get_container()->get(Settings::class);
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            assert($settings instanceof Settings);
            $handlerClass = $settings->get_default_handler();
            if (class_exists($handlerClass)) {
                return $handlerClass;
            }
        } catch (Exception $exception) {
            // Silently handle exception, fall back to default handler.
        }
        // Default fallback for older WooCommerce versions.
        return 'WC_Log_Handler_File';
    }
}
