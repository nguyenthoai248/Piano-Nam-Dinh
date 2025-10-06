<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem;

use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * The Filesystem module.
 */
class FilesystemModule implements ServiceModule
{
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $factories;
    public function __construct()
    {
        $moduleRootDir = dirname(__FILE__, 2);
        $this->factories = (require "{$moduleRootDir}/inc/factories.php")();
    }
    /**
     * @inheritDoc
     */
    public function id() : string
    {
        return 'payoneer-filesystem';
    }
    /**
     * @inheritDoc
     */
    public function services() : array
    {
        return $this->factories;
    }
}
