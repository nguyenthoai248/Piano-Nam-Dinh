<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data;

use Exception;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder\OrderFinderInterface;
use WC_Abstract_Order;
use WC_Admin_Status;
use WP_Error;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector\LogCollectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector\LogCollectorFactory;
/**
 * Collects system status report data for the status report email.
 */
class SystemReportCollector implements SystemReportCollectorInterface
{
    private OrderFinderInterface $orderFinder;
    private LogCollectorFactory $logCollectorFactory;
    /**
     * @param LogCollectorFactory $logCollectorFactory Log collector factory.
     */
    public function __construct(LogCollectorFactory $logCollectorFactory, OrderFinderInterface $orderFinder)
    {
        $this->logCollectorFactory = $logCollectorFactory;
        $this->orderFinder = $orderFinder;
    }
    /**
     * @inheritDoc
     */
    public function collect(SystemReportParamsDTO $params): SystemReportDataDTO
    {
        $statusReport = $this->collectSystemStatusReport();
        $orders = $this->collectOrders($params->getOrderIds());
        $logCollector = $this->logCollector();
        $logContent = $this->collectLogContent($logCollector, $params->getLogDate(), $params->getMaxLogSize());
        return (new SystemReportDataDTO())->setStatusReport($statusReport)->setOrders($orders)->setLogContent($logContent)->setLogDate($params->getLogDate())->setLogSource($logCollector->name());
    }
    /**
     * Collect WooCommerce system status report.
     *
     * @return string Raw system status information.
     */
    private function collectSystemStatusReport(): string
    {
        if (!class_exists('WC_Admin_Status')) {
            return 'Error: WooCommerce Admin Status class not available';
        }
        ob_start();
        try {
            // The status report has a "current_user_can" check, which we need to bypass in cron.
            add_filter('woocommerce_rest_check_permissions', [$this, 'grantReadPermissionToStatusReport'], 10, 4);
            // Get the raw system status report HTML
            WC_Admin_Status::status_report();
            // Restore the original permission check.
            remove_filter('woocommerce_rest_check_permissions', [$this, 'grantReadPermissionToStatusReport']);
            return ob_get_clean() ?: '';
        } catch (Exception $exception) {
            ob_end_clean();
            return 'Error generating system status report: ' . $exception->getMessage();
        }
    }
    /**
     * A custom permission check to allow system status report access in this cron request.
     *
     * @param bool|mixed $permission
     * @param string|mixed $context
     * @param int|mixed $objectId
     * @param string|mixed $object
     *
     * @return bool
     */
    // phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType -- filter callback, types cannot be guaranteed.
    public function grantReadPermissionToStatusReport($permission, $context = '', $objectId = 0, $object = ''): bool
    {
        // We need to grant READ access to the system_status and reports capabilities.
        if ('read' === $context) {
            if ('system_status' === $object || 'reports' === $object) {
                return \true;
            }
        }
        // All other checks return the unmodified decision.
        return (bool) $permission;
    }
    /**
     * Collect order reports for the specified order IDs.
     *
     * @param int[]|string[] $orderIds List of order IDs to include in the report.
     *
     * @return array<string, WC_Abstract_Order|WP_Error> Array of orders/errors, keyed by order ID.
     */
    private function collectOrders(array $orderIds): array
    {
        if (empty($orderIds)) {
            return [];
        }
        $orderReports = [];
        foreach ($orderIds as $orderId) {
            $orderId = (string) $orderId;
            $orders = $this->findMatchingOrders($orderId);
            if (!$orders) {
                $orderReports[$orderId] = new WP_Error('not-found', sprintf('No matching order found for the ID "%s"', $orderId));
                continue;
            }
            foreach ($orders as $order) {
                $orderReports[(string) $order->get_id()] = $order;
            }
        }
        return $orderReports;
    }
    /**
     * Retrieves an order based on the given identifier.
     *
     * @param string $id Identifier, either order-id, or transaction-id.
     *
     * @return WC_Abstract_Order[] A list of matching WC_Orders or WC_Order_Refunds.
     */
    private function findMatchingOrders(string $id): array
    {
        // First, try to find the WC order by its official order ID.
        if (is_numeric($id)) {
            $order = wc_get_order($id);
            if ($order instanceof WC_Abstract_Order) {
                return [$order];
            }
            return [];
        }
        /*
         * Second, use a query to find all WC orders with the relevant meta value.
         * Since the same meta value may be used in multiple orders (theoretically), this query
         * fetches up to 20 orders with the exact meta value match. In practice, this should
         * return a single order.
         */
        return $this->orderFinder->findOrdersByTransactionId($id, 20);
    }
    /**
     * @return LogCollectorInterface The collector class, responsible for providing log data.
     */
    private function logCollector(): LogCollectorInterface
    {
        return $this->logCollectorFactory->collector();
    }
    /**
     * Collect log content from a specific date.
     *
     * @param LogCollectorInterface $collector Instance of the log collector class.
     * @param string $logDate Which date to collect logs for.
     * @param int|null $maxSizeBytes Maximum size of log content to collect.
     *
     * @return string Raw log content.
     */
    private function collectLogContent(LogCollectorInterface $collector, string $logDate, ?int $maxSizeBytes = null): string
    {
        if (empty($logDate)) {
            return '';
        }
        return $collector->collectForDate($logDate, $maxSizeBytes);
    }
}
