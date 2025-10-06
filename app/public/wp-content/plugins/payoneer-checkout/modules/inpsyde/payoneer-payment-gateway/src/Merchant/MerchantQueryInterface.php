<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant;

use Countable;
use RuntimeException;
/**
 * Something that can retrieve Merchants.
 */
interface MerchantQueryInterface
{
    /**
     * Creates an instance with the specified Merchant ID.
     *
     * @param int $id The Merchant ID.
     *
     * @return static A new instance with the specified Merchant ID.
     *
     * @throws RuntimeException If problem.
     */
    public function withId(int $id);
    /**
     * Executes the query to retrieve merchants.
     *
     * @return iterable<MerchantInterface>&Countable The list of merchants.
     *
     * @throws RuntimeException If problem.
     */
    public function execute() : iterable;
}
