<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderAdmin;

use Syde\Vendor\Inpsyde\Assets\Handler\ScriptHandler;
use Syde\Vendor\Inpsyde\Assets\Handler\StyleHandler;
use Syde\Vendor\Inpsyde\Assets\Script;
use Syde\Vendor\Inpsyde\Assets\Style;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNotice;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeRenderer;
/**
 * Provides UI modifications to the WooCommerce "Order Details" page.
 */
class OrderDetailsPage
{
    public const INIT_HOOK = 'add_meta_boxes_woocommerce_page_wc-orders';
    private const ASSET_HANDLE = 'payoneer-order-details';
    private string $mainPluginFile;
    private string $assetsPath;
    private AdminNoticeRenderer $adminNoticeRenderer;
    private bool $isHposEnabled = \false;
    public function __construct(string $mainPluginFile, string $assetsPath, AdminNoticeRenderer $adminNoticeRenderer, bool $isHposEnabled)
    {
        $this->mainPluginFile = $mainPluginFile;
        $this->assetsPath = $assetsPath;
        $this->adminNoticeRenderer = $adminNoticeRenderer;
        $this->isHposEnabled = $isHposEnabled;
    }
    /**
     * Check if the current admin page is the WooCommerce order details page.
     *
     * @return bool True if on the order details page, false otherwise.
     */
    public function isOrderDetailsPage(): bool
    {
        if (!did_action('admin_init')) {
            wc_doing_it_wrong('isOrderDetailsPage', 'This method is not reliable before the "admin_init" action fired.', '3.5.0');
        }
        // No DI: The "current_screen" API is only available after admin_init fired.
        $screen = get_current_screen();
        if (!$screen) {
            return \false;
        }
        if ($this->isHposEnabled) {
            // With HPOS we need to inspect the URL.
            if ($screen->id !== 'woocommerce_page_wc-orders') {
                return \false;
            }
            $getAction = filter_input(\INPUT_GET, 'action', \FILTER_SANITIZE_STRING) ?? '';
            $getId = filter_input(\INPUT_GET, 'id', \FILTER_SANITIZE_NUMBER_INT) ?? '';
            if ('new' === $getAction) {
                return \true;
            }
            return 'edit' === $getAction && (int) $getId > 0;
        }
        // Non-HPOS check can rely on WP_Screen.
        return $screen->id === 'shop_order' && $screen->base === 'post';
    }
    public function disableRefundButton(): void
    {
        add_filter('woocommerce_admin_order_should_render_refunds', '__return_false');
    }
    public function disableOrderStatusChange(): void
    {
        $this->enqueueAssets();
        wp_add_inline_script(self::ASSET_HANDLE, 'document.addEventListener("payoneer:order-page-ready", ({detail: api}) => api.lockStatus())');
    }
    public function renderHeaderNotice(AdminNotice $notice): void
    {
        $renderer = function () use ($notice) {
            $this->adminNoticeRenderer->render($notice);
        };
        $this->addHeaderNoticeRenderer($renderer);
    }
    public function renderDismissibleHeaderNotice(AdminNotice $notice, string $type, int $id = 0): void
    {
        $renderer = function () use ($notice, $type, $id) {
            $this->adminNoticeRenderer->renderDismissible($notice, $type, $id);
        };
        $this->addHeaderNoticeRenderer($renderer);
    }
    public function renderOrderItemNotice(string $message, string $className = ''): void
    {
        $renderer = static function () use ($message, $className) {
            printf('<span class="order-item-detail %s">%s</span>', esc_attr($className), esc_html($message));
        };
        $this->addOrderItemMessageRenderer($renderer);
    }
    private function enqueueAssets(): void
    {
        $baseUrl = plugins_url($this->assetsPath, $this->mainPluginFile);
        $scriptHandler = new ScriptHandler(wp_scripts());
        $styleHandler = new StyleHandler(wp_styles());
        $script = new Script(self::ASSET_HANDLE, $baseUrl . 'order-details.js');
        $scriptHandler->enqueue($script);
        $style = new Style(self::ASSET_HANDLE, $baseUrl . 'order-details.css');
        $styleHandler->enqueue($style);
    }
    /**
     * Renders an HTML element at the end of the "Order Items" sections' footer.
     * To pull the element to the left side, we can use CSS.
     */
    private function addOrderItemMessageRenderer(callable $renderer): void
    {
        $this->enqueueAssets();
        add_action('woocommerce_order_item_add_action_buttons', $renderer);
    }
    /**
     * Hook into the "order details" page to display a notification in the header of the first
     * box with the title "Order #123 details." When a refund is pending or failed, a message
     * is displayed in this section.
     */
    private function addHeaderNoticeRenderer(callable $renderer): void
    {
        add_action('woocommerce_admin_order_data_after_payment_info', $renderer);
    }
}
