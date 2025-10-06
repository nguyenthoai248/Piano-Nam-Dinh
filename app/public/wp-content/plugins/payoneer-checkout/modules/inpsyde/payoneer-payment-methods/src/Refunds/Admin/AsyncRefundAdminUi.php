<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundTextContents;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNotice;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeHooks;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundStateInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service\RefundOrchestratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderAdmin\OrderDetailsPage;
use WC_Order;
/**
 * @todo add test cases for this class.
 */
class AsyncRefundAdminUi
{
    private const NOTICE_TYPE = 'async_refund';
    private RefundTextContents $texts;
    private RefundOrchestratorInterface $refundOrchestrator;
    private RefundStateInterface $refundState;
    private OrderDetailsPage $detailsPage;
    public function __construct(RefundTextContents $texts, RefundOrchestratorInterface $refundOrchestrator, RefundStateInterface $refundState, OrderDetailsPage $detailsPage)
    {
        $this->texts = $texts;
        $this->refundOrchestrator = $refundOrchestrator;
        $this->refundState = $refundState;
        $this->detailsPage = $detailsPage;
    }
    public function init(): void
    {
        // Happens in a REST call, which does not trigger the following INIT_HOOK.
        $this->registerDismissHandler();
        add_action(OrderDetailsPage::INIT_HOOK, function ($wcOrder) {
            if (!$wcOrder instanceof WC_Order) {
                return;
            }
            $this->configureDetailsPage($wcOrder);
        });
    }
    private function configureDetailsPage(WC_Order $wcOrder): void
    {
        $state = $this->refundState->withWcOrder($wcOrder);
        if ($state->isRefundPending()) {
            $this->detailsPage->disableRefundButton();
            $this->detailsPage->disableOrderStatusChange();
            $this->detailsPage->renderHeaderNotice($this->buildPendingNotice());
            $this->detailsPage->renderOrderItemNotice($this->texts->orderRefundProcessingButtonText(), 'refund-status-pending');
        }
        if ($state->didRefundFail()) {
            $this->detailsPage->renderDismissibleHeaderNotice($this->buildFailureNotice(), self::NOTICE_TYPE, $wcOrder->get_id());
        }
    }
    private function registerDismissHandler(): void
    {
        AdminNoticeHooks::onDismiss(self::NOTICE_TYPE, function (int $orderId) {
            $wcOrder = wc_get_order($orderId);
            if (!$wcOrder instanceof WC_Order) {
                return;
            }
            $this->refundOrchestrator->clearFailedRefundState($wcOrder);
        });
    }
    private function buildPendingNotice(): AdminNotice
    {
        return (new AdminNotice())->setType(AdminNotice::TYPE_WARNING)->addClass(AdminNotice::STYLE_ALT)->addClass(AdminNotice::STYLE_INLINE)->setContent($this->texts->orderRefundPendingNotice());
    }
    private function buildFailureNotice(): AdminNotice
    {
        return (new AdminNotice())->setType(AdminNotice::TYPE_ERROR)->addClass(AdminNotice::STYLE_ALT)->addClass(AdminNotice::STYLE_INLINE)->setContent($this->texts->orderRefundFailedNotice());
    }
}
