<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderSecurityValidator;

class OrderSecurityValidator implements OrderSecurityValidatorInterface
{
    /**
     * @var string
     */
    protected $securityHeaderFieldName;
    /**
     * @param string $securityHeaderFieldName
     */
    public function __construct(string $securityHeaderFieldName)
    {
        $this->securityHeaderFieldName = $securityHeaderFieldName;
    }
    /**
     * @inheritDoc
     */
    public function orderTokenValid(\WC_Order $order, string $token) : bool
    {
        $expectedHeaderValue = (string) $order->get_meta($this->securityHeaderFieldName, \true);
        return $this->securityHeaderFieldName === $expectedHeaderValue;
    }
}
