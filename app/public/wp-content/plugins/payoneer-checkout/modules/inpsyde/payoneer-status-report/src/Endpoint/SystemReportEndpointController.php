<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Endpoint;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportParamsDTO;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\WpRestApiControllerInterface;
use WP_REST_Request;
use WP_REST_Response;
/**
 * Handles the system report REST API requests.
 */
class SystemReportEndpointController implements WpRestApiControllerInterface
{
    /**
     * URL parameter used for retrieving order IDs for the report.
     * Optional, numeric list. Format: `123,456,789`.
     */
    private const PARAM_ORDER_IDS = 'orders';
    /**
     * URL parameter to attach a log file to the report.
     * Optional, string. Format: `yyyy-mm-dd`.
     */
    private const PARAM_LOG_DATE = 'log_date';
    /**
     * Starts or schedules the background processing for the system report.
     *
     * @var callable
     */
    private $backgroundProcessor;
    /**
     * @param callable $backgroundProcessor
     */
    public function __construct(callable $backgroundProcessor)
    {
        $this->backgroundProcessor = $backgroundProcessor;
    }
    /**
     * @inheritDoc
     */
    public function handleWpRestRequest(WP_REST_Request $request): WP_REST_Response
    {
        // Get request parameters.
        $orderIds = $this->getOrderIds($request);
        $logDate = $this->getLogDate($request);
        // Prepare parameters for the background processor.
        $params = (new SystemReportParamsDTO())->setOrderIds($orderIds)->setLogDate($logDate);
        // Schedule the task.
        $processor = $this->backgroundProcessor;
        $result = (array) $processor($params);
        if (!$result['success']) {
            return new WP_REST_Response(['success' => \false, 'message' => $result['message'] ?? 'Failed to schedule system report'], 500);
        }
        // Instantly trigger the background processor task in a detached cron request.
        spawn_cron();
        return new WP_REST_Response(['success' => \true, 'message' => 'System report scheduled'], 200);
    }
    /**
     * Extracts the order IDs from the request parameters.
     *
     * @param WP_REST_Request $request The incoming request.
     *
     * @return string[] An array of unique order IDs, or an empty array.
     */
    private function getOrderIds(WP_REST_Request $request): array
    {
        $orderIdString = trim((string) $request->get_param(self::PARAM_ORDER_IDS));
        if (empty($orderIdString)) {
            return [];
        }
        $orderIds = array_map('sanitize_text_field', explode(',', $orderIdString));
        // Remove empty values and duplicates.
        return array_unique(array_filter($orderIds));
    }
    /**
     * Extracts the log date from the request parameters.
     *
     * @param WP_REST_Request $request The incoming request.
     *
     * @return string A date string in the format 'Y-m-d' or an empty string (invalid/missing).
     */
    private function getLogDate(WP_REST_Request $request): string
    {
        $logDate = trim((string) $request->get_param(self::PARAM_LOG_DATE));
        if (empty($logDate)) {
            return '';
        }
        $timestamp = strtotime($logDate);
        if ($timestamp === \false) {
            return '';
        }
        return date('Y-m-d', $timestamp);
    }
}
