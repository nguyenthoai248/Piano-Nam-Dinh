<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

/**
 * "Checkout merchants" are a unique concept in Payoneer and represent
 * a specific class of merchant accounts.
 * This differs from the usual "Orchestration merchant" in that they (currently) need to use
 * a different dashboard.
 * For us, this means using a different URL template when creating orders,
 * which is solved by this decorator.
 */
class CheckoutMerchantAwareUrlTemplateProvidingMerchant extends AbstractMerchantDecorator
{
    /**
     * @var string
     */
    protected $urlTemplate;
    /**
     * @param string $urlTemplateMap The URL Template
     * @param MerchantInterface $merchant
     */
    public function __construct(string $urlTemplateMap, MerchantInterface $merchant)
    {
        $this->urlTemplate = $urlTemplateMap;
        parent::__construct($merchant);
    }
    /**
     * All merchant account codes are prefixed by "MRS_"
     * which we inspect when the url template is queried
     *
     * @return string
     */
    public function getTransactionUrlTemplate() : string
    {
        if (strpos($this->getCode(), 'MRS_') === 0) {
            return 'https://myaccount.payoneer.com/ma/checkout/transactions';
        }
        return $this->merchant->getTransactionUrlTemplate();
    }
}
