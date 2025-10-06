<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AdminBanner;

interface AdminBannerRendererInterface
{
    /**
     * Render an admin banner on the current page.
     */
    public function renderBanner(): void;
}
