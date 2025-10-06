<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant;

use Syde\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
/**
 * Something that can create a merchant from merchant details.
 */
interface MerchantFactoryInterface
{
    /**
     * Creates a new Merchant.
     *
     * @param ?positive-int $id The ID, if any.
     * @param string $environment The environment.
     * @param string $code The code.
     * @param string $token The token.
     * @param string|UriInterface $baseUrl The base URL of the API instance, to which the merchant belongs.
     * @param string $transactionUrlTemplate The template for transaction URLs for the API instance.
     * @param string $label The human-readable label.
     * @param string $division The code.
     *
     * @return MerchantInterface The new Merchant.
     *
     * @throws RuntimeException If problem creating.
     */
    public function createMerchant(?int $id, string $environment = '', string $code = '', string $token = '', $baseUrl = '', string $transactionUrlTemplate = '', string $label = '', string $division = ''): MerchantInterface;
}
