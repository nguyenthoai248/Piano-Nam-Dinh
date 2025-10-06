<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
class CompoundPaymentFieldsRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var PaymentFieldsRendererInterface[]
     */
    protected $renderers;
    public function __construct(PaymentFieldsRendererInterface ...$renderers)
    {
        $this->renderers = $renderers;
    }
    public function renderFields(): string
    {
        $result = '';
        foreach ($this->renderers as $renderer) {
            $result .= $renderer->renderFields();
        }
        return $result;
    }
}
