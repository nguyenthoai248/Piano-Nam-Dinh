<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp;

use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\FactoryModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice\AdminNoticeRestEndpoint;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * The WP core features module.
 */
class WpModule implements ServiceModule, FactoryModule, ExecutableModule
{
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected array $services;
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected array $factories;
    public function __construct()
    {
        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "{$moduleRootDir}/inc/services.php")();
        $this->factories = (require "{$moduleRootDir}/inc/factories.php")();
    }
    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return 'payoneer-wp';
    }
    /**
     * @inheritDoc
     */
    public function services(): array
    {
        return $this->services;
    }
    public function factories(): array
    {
        return $this->factories;
    }
    public function run(ContainerInterface $container): bool
    {
        /** @var callable():void $addTransactionIdFieldSupport */
        $addTransactionIdFieldSupport = $container->get('wp.add_transaction_id_field_support');
        $addTransactionIdFieldSupport();
        /** @var callable():void $addPayoutIdFieldSupport */
        $addPayoutIdFieldSupport = $container->get('wp.add_payout_id_field_support');
        $addPayoutIdFieldSupport();
        $this->setupAdminNotices($container);
        return \true;
    }
    private function setupAdminNotices(ContainerInterface $container): void
    {
        $restEndpoint = $container->get('wp.admin_notice.dismiss_rest_endpoint');
        assert($restEndpoint instanceof AdminNoticeRestEndpoint);
        add_action('rest_api_init', [$restEndpoint, 'registerRoute']);
    }
}
