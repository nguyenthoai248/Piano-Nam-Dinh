<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
/**
 * Utility class to make sure fields are only rendered once.
 * Useful when fields with the same name are used in multiple gateways
 */
class RenderOnceFieldRenderer implements PaymentFieldsRendererInterface
{
    public bool $rendered = \false;
    public PaymentFieldsRendererInterface $renderer;
    /**
     * @param PaymentFieldsRendererInterface $renderer
     */
    public function __construct(PaymentFieldsRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
    public function renderFields(): string
    {
        if ($this->rendered) {
            return "";
        }
        try {
            return $this->renderer->renderFields();
        } finally {
            $this->rendered = \true;
        }
    }
}
