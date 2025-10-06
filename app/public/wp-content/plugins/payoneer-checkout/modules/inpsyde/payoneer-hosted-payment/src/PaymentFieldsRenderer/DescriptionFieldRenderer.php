<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
class DescriptionFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var string
     */
    protected $description;
    public function __construct(string $description)
    {
        $this->description = $description;
    }
    public function renderFields(): string
    {
        return $this->description;
    }
}
