<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder;

/**
 * Order finder working with WooCommerce HPOS - High Performance Order Storage.
 */
class HposOrderFinder extends AbstractOrderFinder implements OrderFinderInterface
{
    public function args(string $transactionId): array
    {
        return ['limit' => 1, 'meta_query' => [['key' => $this->transactionIdOrderFieldName, 'value' => $transactionId]]];
    }
}
