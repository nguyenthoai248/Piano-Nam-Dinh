<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use RuntimeException;
/**
 * Something that can convert a Merchant into its DTO.
 *
 * @psalm-type MerchantData = array{
 *  id: ?positive-int,
 *  label: string,
 *  code: string,
 *  token: string,
 *  base_url: string,
 *  transaction_url_template: string
 * }
 */
interface MerchantSerializerInterface
{
    /**
     * Transforms a Merchant into its TDO.
     *
     * @param MerchantInterface $merchant
     *
     * @return MerchantData The Merchant data.
     *
     * @throws RuntimeException If problem transforming.
     */
    public function serializeMerchant(MerchantInterface $merchant) : array;
}
