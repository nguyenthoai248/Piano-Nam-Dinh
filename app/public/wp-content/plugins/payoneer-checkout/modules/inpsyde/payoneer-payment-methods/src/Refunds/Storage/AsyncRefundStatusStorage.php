<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order;
/**
 * @see AsyncRefundStatusStorageTest - unit tests for this class.
 */
class AsyncRefundStatusStorage implements AsyncRefundStatusStorageInterface
{
    private const META_KEY = '_payoneer-refund-status';
    public function currentRefundStatus(WC_Order $wcOrder): ?string
    {
        $status = $wcOrder->get_meta(self::META_KEY, \true);
        if (!is_string($status) || '' === $status) {
            return null;
        }
        return $status;
    }
    public function changeRefundStatusTo(WC_Order $wcOrder, ?string $status): void
    {
        if (is_null($status) || '' === $status) {
            $this->clearRefundStatus($wcOrder);
            return;
        }
        $wcOrder->update_meta_data(self::META_KEY, $status);
        $wcOrder->save_meta_data();
    }
    public function clearRefundStatus(WC_Order $wcOrder): void
    {
        $wcOrder->delete_meta_data(self::META_KEY);
        $wcOrder->save_meta_data();
    }
}
