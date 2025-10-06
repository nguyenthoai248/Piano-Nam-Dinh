<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Storage;

use WC_Order;
use WC_Order_Item;
use WC_Order_Refund;
/**
 * @todo Not tested, as it's not fully implemented yet.
 */
class AsyncRefundIntentStorage implements AsyncRefundIntentStorageInterface
{
    private const META_KEY = '_payoneer-refund-intent';
    public function storeRefundIntent(WC_Order $wcOrder, WC_Order_Refund $wcRefund): void
    {
        $serialized = $this->extractDataFromRefund($wcRefund);
        $wcOrder->update_meta_data(self::META_KEY, $serialized);
        $wcOrder->save_meta_data();
    }
    public function refundIntent(WC_Order $wcOrder): ?WC_Order_Refund
    {
        if (!$this->hasRefundIntent($wcOrder)) {
            return null;
        }
        $data = $this->serializedIntent($wcOrder);
        $wcRefund = $this->restoreRefundFromData($data);
        $wcRefund->set_parent_id($wcOrder->get_id());
        return $wcRefund;
    }
    public function clearRefundIntent(WC_Order $wcOrder): void
    {
        $wcOrder->delete_meta_data(self::META_KEY);
        $wcOrder->save_meta_data();
    }
    public function hasRefundIntent(WC_Order $wcOrder): bool
    {
        $data = $this->serializedIntent($wcOrder);
        return !empty($data);
    }
    private function serializedIntent(WC_Order $wcOrder): array
    {
        $data = $wcOrder->get_meta(self::META_KEY, \true);
        return is_array($data) ? $data : [];
    }
    private function extractDataFromRefund(WC_Order_refund $wcRefund): array
    {
        return ['id' => $wcRefund->get_id(), 'amount' => $wcRefund->get_amount(), 'reason' => $wcRefund->get_reason(), 'line_items' => $wcRefund->get_items(['line_item', 'fee', 'shipping'])];
    }
    private function restoreRefundFromData(array $data): WC_Order_Refund
    {
        /**
         * If a valid ID is present, then load and use the actual refund from
         * the DB instead of creating a new one. This prevents duplicate DB
         * entries, and ensures the restored refund reflects actual DB values.
         */
        if (!empty($data['id'])) {
            $existingRefund = wc_get_order($data['id']);
            if ($existingRefund instanceof WC_Order_Refund) {
                return $existingRefund;
            }
        }
        $newRefund = new WC_Order_Refund();
        $newRefund->set_amount((string) ($data['amount'] ?? '0'));
        // WC expects a string.
        $newRefund->set_reason((string) ($data['reason'] ?? ''));
        foreach ($data['line_items'] ?? [] as $item) {
            if ($item instanceof WC_Order_Item) {
                $newRefund->add_item($item);
            }
        }
        return $newRefund;
    }
}
