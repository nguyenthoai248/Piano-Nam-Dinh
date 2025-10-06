<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder;

class OrderFinder extends AbstractOrderFinder implements OrderFinderInterface
{
    public function args(string $transactionId): array
    {
        return ['limit' => 1, 'type' => 'shop_order', $this->transactionIdOrderFieldName => $transactionId];
    }
}
