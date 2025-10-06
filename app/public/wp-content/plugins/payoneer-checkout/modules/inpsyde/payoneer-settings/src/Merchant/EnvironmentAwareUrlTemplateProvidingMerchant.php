<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant;

class EnvironmentAwareUrlTemplateProvidingMerchant extends AbstractMerchantDecorator
{
    /**
     * @var array
     */
    protected $urlTemplateMap;
    /**
     * @param array $urlTemplateMap Map of 'environment' => 'urlTemplate'
     * @param MerchantInterface $merchant
     */
    public function __construct(array $urlTemplateMap, MerchantInterface $merchant)
    {
        $this->urlTemplateMap = $urlTemplateMap;
        parent::__construct($merchant);
    }
    public function getTransactionUrlTemplate(): string
    {
        $environment = $this->getEnvironment();
        if (isset($this->urlTemplateMap[$environment])) {
            /**
             * Add the 'merchant' GET param using the merchant code
             */
            return add_query_arg(['merchant' => $this->getCode()], $this->urlTemplateMap[$environment]);
        }
        return $this->merchant->getTransactionUrlTemplate();
    }
}
