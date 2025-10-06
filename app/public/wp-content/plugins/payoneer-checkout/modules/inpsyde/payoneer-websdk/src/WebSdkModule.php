<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk;

use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
class WebSdkModule implements ServiceModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     */
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
