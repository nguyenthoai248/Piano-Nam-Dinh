<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Migration;

use Exception;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\Modularity\Package;
use Syde\Vendor\Inpsyde\Modularity\Properties\PluginProperties;
use Syde\Vendor\Psr\Container\ContainerInterface;
class MigrationModule implements ExecutableModule, ServiceModule
{
    use ModuleClassNameIdTrait;
    /**
     * @throws Exception
     */
    public function run(ContainerInterface $container): bool
    {
        $callback = static function () use ($container): void {
            /** @var PluginProperties $properties */
            $properties = $container->get(Package::PROPERTIES);
            $pluginVersion = $properties->version();
            if (!version_compare($pluginVersion, '0.0.1', '>=')) {
                return;
                //this is a development version.
            }
            $pluginVersionOptionName = (string) $container->get('migration.plugin_version_option_name');
            $dbPluginVersion = (string) get_option($pluginVersionOptionName);
            if (empty($dbPluginVersion)) {
                /**
                 * This appears to be a fresh install. Just update the DB version and bail
                 */
                update_option($pluginVersionOptionName, $pluginVersion);
                return;
            }
            if (version_compare($pluginVersion, $dbPluginVersion, '>')) {
                /** @var MigratorInterface $migrator */
                $migrator = $container->get('migration.migrator');
                $migrator->migrate();
                update_option($pluginVersionOptionName, $pluginVersion);
            }
        };
        add_action('init', $callback);
        return \true;
    }
    public function services(): array
    {
        static $services;
        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }
        /** @var callable(): array<string, callable(\Psr\Container\ContainerInterface $container):mixed> $services */
        return $services();
    }
}
