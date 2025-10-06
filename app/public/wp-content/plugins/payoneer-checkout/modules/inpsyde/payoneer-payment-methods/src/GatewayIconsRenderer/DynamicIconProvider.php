<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\GatewayIconsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\Icon;
use Syde\Vendor\Inpsyde\PaymentGateway\IconProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
/**
 * DynamicIconProvider is responsible for dynamically generating a list of icons based on the
 * available networks and a predefined network map.
 * It fetches applicable networks from a provided ListInterface object,
 * maps them to corresponding icon definitions using the networkMap array,
 * and returns an array of these icons. If any exceptions occur during this process,
 * they are currently ignored.
 */
class DynamicIconProvider implements IconProviderInterface
{
    protected ListSessionProvider $listSessionProvider;
    /** @var array<string, string> $networkMap */
    protected array $networkMap;
    protected IconProviderInterface $default;
    /**
     * @param ListSessionProvider $listSessionProvider
     * @param array<string, string> $networkMap
     * @param IconProviderInterface $default
     */
    public function __construct(ListSessionProvider $listSessionProvider, array $networkMap, IconProviderInterface $default)
    {
        $this->listSessionProvider = $listSessionProvider;
        $this->networkMap = $networkMap;
        $this->default = $default;
    }
    /**
     * Provides an array of icons based on the available networks and network map.
     *
     * Iterates over applicable networks, maps them to icon definitions using the networkMap,
     * and returns a list of these icons. Exceptions during this process are currently ignored
     *
     * @return Icon[] Array of icon definitions corresponding to applicable networks.
     */
    public function provideIcons(): array
    {
        $result = [];
        $defaultIcons = $this->default->provideIcons();
        /**
         * Create an associative array of Icons by plucking their "id" as key
         *
         * @var array<string|Icon> $iconsById
         */
        $iconsById = array_combine(array_map(fn(Icon $icon) => $icon->id(), $defaultIcons), $defaultIcons);
        try {
            foreach ($this->getList()->getNetworks()->getApplicable() as $network) {
                $iconId = $this->networkMap[$network->getCode()] ?? '';
                $icon = $iconsById[$iconId] ?? null;
                if ($icon instanceof Icon) {
                    $result[] = $icon;
                }
            }
            return $result;
        } catch (\Throwable $exception) {
            //TODO logging
            return $defaultIcons;
        }
    }
    /**
     * Returns the List session object from the configured provider
     *
     * @return ListInterface
     */
    private function getList(): ListInterface
    {
        return $this->listSessionProvider->provide(new PaymentContext());
    }
}
