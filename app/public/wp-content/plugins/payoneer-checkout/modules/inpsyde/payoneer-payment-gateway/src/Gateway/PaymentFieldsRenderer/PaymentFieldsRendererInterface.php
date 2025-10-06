<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer;

/**
 * Service able to return string.
 */
interface PaymentFieldsRendererInterface
{
    /**
     * Render container for payment fields.
     *
     * @return string Rendered HTML.
     * @throws \Throwable
     */
    public function renderFields() : string;
}
