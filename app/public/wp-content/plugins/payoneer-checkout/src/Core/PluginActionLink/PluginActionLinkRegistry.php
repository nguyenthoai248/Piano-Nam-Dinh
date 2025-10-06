<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\PluginActionLink;

class PluginActionLinkRegistry
{
    /**
     * @var string
     */
    private $pluginMainFile;
    /**
     * @var PluginActionLink[]
     */
    private $links;
    public function __construct(string $pluginMainFile, PluginActionLink ...$links)
    {
        $this->pluginMainFile = $pluginMainFile;
        $this->links = $links;
    }
    public function init(): void
    {
        add_filter('plugin_action_links', function (array $links, string $pluginFile) {
            if ($pluginFile !== $this->pluginMainFile) {
                return $links;
            }
            $added = [];
            foreach ($this->links as $linkObject) {
                $added[$linkObject->slug()] = $linkObject;
            }
            return array_merge($added, $links);
        }, 10, 2);
    }
}
