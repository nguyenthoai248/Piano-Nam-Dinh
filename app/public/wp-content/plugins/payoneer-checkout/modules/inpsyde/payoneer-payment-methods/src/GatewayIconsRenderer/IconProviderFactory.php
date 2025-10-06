<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\GatewayIconsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\Icon;
use Syde\Vendor\Inpsyde\PaymentGateway\IconProviderInterface;
use Syde\Vendor\Inpsyde\PaymentGateway\StaticIconProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
class IconProviderFactory
{
    protected string $mainPluginFile;
    protected string $assetPath;
    /** @var callable */
    protected $canTryCreateList;
    protected ListSessionProvider $listSessionProvider;
    /** @var array<string, string> */
    protected array $iconMap;
    /**
     * @param array<string, string> $networkMap
     */
    public function __construct(string $mainPluginFile, string $assetPath, callable $canTryCreateList, ListSessionProvider $listSessionProvider, array $networkMap)
    {
        $this->mainPluginFile = $mainPluginFile;
        $this->assetPath = $assetPath;
        $this->canTryCreateList = $canTryCreateList;
        $this->listSessionProvider = $listSessionProvider;
        $this->iconMap = $networkMap;
    }
    public function create(array $defaultIcons): IconProviderInterface
    {
        $src = fn(string $handle) => plugins_url("{$this->assetPath}/img/{$handle}.svg", $this->mainPluginFile);
        $alt = static fn(string $handle) => "{$handle} icon";
        $createIcon = static fn(string $handle) => new Icon($handle, $src($handle), $alt($handle));
        $defaultIconProvider = new StaticIconProvider(...array_map($createIcon, $defaultIcons));
        /**
         * If it is safe to boot a LIST, we can inspect real data
         */
        if (!($this->canTryCreateList)()) {
            return $defaultIconProvider;
        }
        /**
         * @var array<string, string> $networkMap
         */
        return new DynamicIconProvider($this->listSessionProvider, $this->iconMap, $defaultIconProvider);
    }
}
