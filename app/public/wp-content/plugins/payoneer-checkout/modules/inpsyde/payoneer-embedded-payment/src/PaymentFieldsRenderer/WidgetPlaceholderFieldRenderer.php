<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
/**
 * Render payment fields that should be displayed on checkout.
 */
class WidgetPlaceholderFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * ID of the HTML element used as a container for payment fields.
     *
     * @var string
     */
    protected $paymentFieldsContainerId;
    /**
     * @var string
     */
    protected $paymentFieldsDropInComponentAttribute;
    /**
     * @var string
     */
    protected $paymentFieldsDropInComponent;
    protected string $description;
    /**
     * @param string $paymentFieldsContainerId ID of the HTML element used as a container for
     *          payment fields.
     */
    public function __construct(string $paymentFieldsContainerId, string $paymentFieldsDropInComponentAttribute, string $paymentFieldsDropInComponent, string $description)
    {
        $this->paymentFieldsContainerId = $paymentFieldsContainerId;
        $this->paymentFieldsDropInComponentAttribute = $paymentFieldsDropInComponentAttribute;
        $this->paymentFieldsDropInComponent = $paymentFieldsDropInComponent;
        $this->description = $description;
    }
    /**
     * @inheritDoc
     */
    public function renderFields(): string
    {
        //We place a <p></p> to differentiate from the <div></div> iframe
        return sprintf('<div class="%1$s" %3$s="%4$s"><p>%2$s</p></div>', esc_attr($this->paymentFieldsContainerId), $this->description, $this->paymentFieldsDropInComponentAttribute, $this->paymentFieldsDropInComponent);
    }
}
