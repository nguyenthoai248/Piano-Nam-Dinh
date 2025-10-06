<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport;

use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportCollectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportParamsDTO;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email\SystemReportEmailSenderInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * @psalm-import-type StatusReportItem from Renderer
 */
class StatusReportModule implements ServiceModule, ExecutableModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $services;
    public function __construct()
    {
        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "{$moduleRootDir}/inc/services.php")();
    }
    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return 'payoneer-status-report';
    }
    /**
     * @inheritDoc
     */
    public function services(): array
    {
        return $this->services;
    }
    public function run(ContainerInterface $container): bool
    {
        /**
         * Extend the default WooCommerce system status report.
         *
         * @see /wp-admin/admin.php?page=wc-status
         */
        add_action('woocommerce_system_status_report', static function () use ($container) {
            $statusReportRenderer = $container->get('status-report.renderer');
            assert($statusReportRenderer instanceof Renderer);
            /** @var StatusReportItem[] $statusReportItems */
            $statusReportItems = $container->get('status-report.fields');
            echo wp_kses_post($statusReportRenderer->render(esc_html__('Payoneer Checkout', 'payoneer-checkout'), $statusReportItems));
        });
        // Register REST API endpoint
        add_action('rest_api_init', static function () use ($container) {
            /** @var string $namespace */
            $namespace = $container->get('status-report.namespace');
            /** @var string $route */
            $route = $container->get('status-report.rest_route');
            /** @var string[] $methods */
            $methods = $container->get('status-report.allowed_methods');
            /** @var callable $callback */
            $callback = $container->get('status-report.callback');
            /** @var callable(): bool $permissionCallback */
            $permissionCallback = $container->get('status-report.permission_callback');
            /**
             * The REST endpoint which triggers the system report mail.
             *
             * Endpoint:
             * POST /wp-json/inpsyde/payoneer-checkout/system-report
             */
            register_rest_route($namespace, $route, ['methods' => $methods, 'callback' => $callback, 'permission_callback' => $permissionCallback]);
        });
        // Cron event handler.
        add_action('payoneer_process_system_report', static function (SystemReportParamsDTO $params) use ($container) {
            $collector = $container->get('status-report.collector');
            assert($collector instanceof SystemReportCollectorInterface);
            $emailSender = $container->get('status-report.email.sender');
            assert($emailSender instanceof SystemReportEmailSenderInterface);
            $reportData = $collector->collect($params);
            $emailSender->sendReport($reportData, $params);
        });
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        return ['payoneer_settings.settings_fields' => static function (array $previous, ContainerInterface $container): array {
            /** @var array $settingsFields */
            $settingsFields = $container->get('status-report.settings.fields');
            return array_merge($previous, $settingsFields);
        }];
    }
}
