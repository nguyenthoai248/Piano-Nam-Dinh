<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use RuntimeException;
/**
 * Something that can convert a DTO into a Merchant.
 *
 * @psalm-type MerchantData = array{
 *  id?: ?positive-int,
 *  label?: string,
 *  environment?: string,
 *  code?: string,
 *  division?: string,
 *  token?: string,
 *  base_url?: string,
 *  transaction_url_template?: string
 * }
 */
interface MerchantDeserializerInterface
{
    /**
     * Transforms a DTO into a Merchant.
     *
     * @param array $dto The Merchant data.
     *
     * @psalm-param MerchantData $dto The Merchant data.
     *
     * @return MerchantInterface The Merchant.
     *
     * @throws RuntimeException If problem transforming.
     */
    public function deserializeMerchant(array $dto) : MerchantInterface;
}
