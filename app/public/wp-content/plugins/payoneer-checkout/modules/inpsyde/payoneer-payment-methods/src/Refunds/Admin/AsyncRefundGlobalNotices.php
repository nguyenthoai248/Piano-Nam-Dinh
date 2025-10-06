<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin;

use Automattic\WooCommerce\Enums\OrderStatus;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundTextContents;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage\AsyncFailedRefundRegistryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNotice;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeRenderer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderAdmin\OrderDetailsPage;
use WC_Order;
/**
 * @todo add test cases for this class.
 */
class AsyncRefundGlobalNotices
{
    private RefundTextContents $texts;
    private AsyncFailedRefundRegistryInterface $registry;
    private AdminNoticeRenderer $renderer;
    private OrderDetailsPage $detailsPage;
    public function __construct(RefundTextContents $texts, AsyncFailedRefundRegistryInterface $registry, AdminNoticeRenderer $renderer, OrderDetailsPage $detailsPage)
    {
        $this->texts = $texts;
        $this->registry = $registry;
        $this->renderer = $renderer;
        $this->detailsPage = $detailsPage;
    }
    public function init(): void
    {
        // Early bail if no failed refunds are present in the registry.
        // This check is very efficient.
        if (!$this->registry->hasFailedOrders()) {
            return;
        }
        add_action('admin_notices', function () {
            // Global failure notices must be hidden on order details pages.
            if ($this->detailsPage->isOrderDetailsPage()) {
                return;
            }
            $this->renderFailedOrderNotices();
        });
    }
    private function renderFailedOrderNotices(): void
    {
        $failedIds = $this->registry->failedOrderIds();
        foreach ($failedIds as $failedId) {
            $orderId = $this->verifyOrderId((int) $failedId);
            if (!$orderId) {
                continue;
            }
            $notice = $this->buildFailureNotice($orderId);
            $this->renderer->render($notice);
        }
    }
    /**
     * Verify if the provided order is a valid WC_Order ID.
     *
     * This check ensures we only display notifications for orders that are actually present
     * and have a failed-refund state.
     *
     * In rare cases, the failed-refund-registry can get outdated (mainly when performing
     * manual DB actions, like restoring a backup or similar).
     *
     * @todo Consider to move this check into the registry
     */
    private function verifyOrderId(int $orderId): int
    {
        $wcOrder = wc_get_order($orderId);
        if (!$wcOrder instanceof WC_Order) {
            $this->registry->removeFailedOrder($orderId);
            return 0;
        }
        if ($orderId !== $wcOrder->get_id()) {
            $this->registry->removeFailedOrder($orderId);
            return 0;
        }
        if ($wcOrder->has_status([OrderStatus::AUTO_DRAFT, OrderStatus::TRASH])) {
            $this->registry->removeFailedOrder($orderId);
            return 0;
        }
        return $wcOrder->get_id();
    }
    private function buildFailureNotice(int $orderId): AdminNotice
    {
        return (new AdminNotice())->setType(AdminNotice::TYPE_ERROR)->setContent($this->texts->globalRefundFailedNotice($orderId));
    }
}
