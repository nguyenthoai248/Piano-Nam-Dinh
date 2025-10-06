<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings;

use Syde\Vendor\Inpsyde\PaymentGateway\GatewayIconsRendererInterface;
class PaymentSettingsPageIconsRenderer implements GatewayIconsRendererInterface
{
    protected array $icons;
    public function __construct(array $icons)
    {
        $this->icons = $icons;
    }
    /**
     * @inheritDoc
     */
    public function renderIcons(): string
    {
        $iconImages = $this->prepareIconImgElements();
        $icons = implode('', $iconImages);
        return sprintf('<span class="payoneer-gateway-icons">%1$s</span>', $icons);
    }
    /**
     * @return string[]
     */
    protected function prepareIconImgElements(): array
    {
        return array_map(function (string $imgPath): string {
            $altText = $this->prepareIconAlt($imgPath);
            $img = sprintf('<img alt="%1$s" src="%2$s">', $altText, $imgPath);
            return $img;
        }, $this->icons);
    }
    protected function prepareIconAlt(string $imgPath): string
    {
        $imgName = basename($imgPath, '.svg');
        return sprintf('%1$s icon', $imgName);
    }
}
