<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use RuntimeException;
/**
 * Something that can save a Merchant.
 */
interface SaveMerchantCommandInterface
{
    /**
     * Persists a merchant.
     *
     * @param MerchantInterface $merchant The merchant to persist.
     *
     * @return MerchantInterface The persisted merchant.
     *
     * @throws RuntimeException If problem persisting.
     */
    public function saveMerchant(MerchantInterface $merchant) : MerchantInterface;
}
