<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * Facilitates caching.
 */
class CacheModule implements ServiceModule
{
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $services;
    public function __construct()
    {
        // TODO: Consider injecting values into a generic module instead
        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "{$moduleRootDir}/inc/services.php")();
    }
    /**
     * @inheritDoc
     */
    public function id() : string
    {
        return 'payoneer-cache';
    }
    /**
     * @inheritDoc
     */
    public function services() : array
    {
        return $this->services;
    }
}
