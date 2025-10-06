<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core;

use Syde\Vendor\Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\PluginActionLink\PluginActionLinkRegistry;
use Syde\Vendor\Psr\Container\ContainerInterface;
class CoreModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     * @throws ValidationFailedExceptionInterface
     */
    public function run(ContainerInterface $container): bool
    {
        add_action('pre_current_active_plugins', static function () use ($container) {
            /** @var PluginActionLinkRegistry $pluginActionLinksRegistry */
            $pluginActionLinksRegistry = $container->get('core.plugin.plugin_action_links.registry');
            $pluginActionLinksRegistry->init();
        });
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function services(): array
    {
        static $services;
        $moduleRootPath = dirname(__DIR__, 2);
        if ($services === null) {
            $services = require_once "{$moduleRootPath}/inc/services.php";
        }
        /** @var callable(string): array<string, callable(ContainerInterface $container):mixed> $services */
        return $services($moduleRootPath);
    }
    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        static $extensions;
        $moduleRootPath = dirname(__DIR__, 2);
        if ($extensions === null) {
            $extensions = require_once "{$moduleRootPath}/inc/extensions.php";
        }
        /** @var callable(string): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions($moduleRootPath);
    }
}
