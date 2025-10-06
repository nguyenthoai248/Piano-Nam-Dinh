<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice;

use Syde\Vendor\Inpsyde\Assets\Handler\ScriptHandler;
use Syde\Vendor\Inpsyde\Assets\Script;
class AdminNoticeRenderer
{
    private string $mainPluginFile;
    private string $assetsPath;
    private AdminNoticeRestEndpoint $restEndpoint;
    public function __construct(string $mainPluginFile, string $assetsPath, AdminNoticeRestEndpoint $restEndpoint)
    {
        $this->mainPluginFile = $mainPluginFile;
        $this->assetsPath = $assetsPath;
        $this->restEndpoint = $restEndpoint;
    }
    public function render(AdminNotice $notice): void
    {
        $this->renderHtml($notice);
    }
    public function renderDismissible(AdminNotice $notice, string $dismissType, int $dismissId = 0): void
    {
        $this->enqueueAssets();
        $this->renderHtml($notice, \true, $dismissType, $dismissId);
    }
    private function renderHtml(AdminNotice $notice, bool $isDismissible = \false, ?string $dismissType = null, int $dismissId = 0): void
    {
        /** @var string[] $classes */
        $classes = array_merge(['notice', 'payoneer-notice'], $notice->getClasses());
        if ($isDismissible && !is_null($dismissType)) {
            $classes[] = 'is-dismissible';
            $dismissData = ['type' => $dismissType, 'id' => $dismissId];
            printf(
                // phpcs:ignore Inpsyde.CodeQuality.LineLength.TooLong
                '<div class="%s" data-dismiss-data="%s" data-dismiss-path="%s"><p>%s <button type="button" class="notice-dismiss"></button></p></div>',
                esc_attr(implode(' ', $classes)),
                esc_attr((string) wp_json_encode($dismissData)),
                esc_attr($this->restEndpoint->endpointPath()),
                wp_kses_post($notice->getContent())
            );
            return;
        }
        printf('<div class="%s"><p>%s</p></div>', esc_attr(implode(' ', $classes)), wp_kses_post($notice->getContent()));
    }
    private function enqueueAssets(): void
    {
        $baseUrl = plugins_url($this->assetsPath, $this->mainPluginFile);
        $scriptHandler = new ScriptHandler(wp_scripts());
        $script = new Script('payoneer-admin-notice', $baseUrl . 'admin-notice.js');
        $scriptHandler->enqueue($script);
    }
}
