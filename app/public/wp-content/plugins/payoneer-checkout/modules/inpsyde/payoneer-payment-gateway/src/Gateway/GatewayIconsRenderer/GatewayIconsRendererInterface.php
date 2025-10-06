<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\GatewayIconsRenderer;

/**
 * Service able to return string.
 */
interface GatewayIconsRendererInterface
{
    /**
     * Render gateway icons.
     *
     * @return string Rendered HTML.
     */
    public function renderIcons() : string;
}
