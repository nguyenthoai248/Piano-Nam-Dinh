<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class PayForOrderTotalsManager
{
    public function __invoke(array $totals, ListInterface $list, bool $isEmailTemplate) : array
    {
        /**
         * Copy all the fields into te new array.
         * Additionally add two new fields for tax and order_total.
         * JS and CSS take care of the fields toggling.
         */
        $payoneerTotals = [];
        $payoneerTaxAlreadyAdded = \false;
        /** @var array{label:string, value:string} $total */
        foreach ($totals as $totalLineId => $total) {
            if (!$isEmailTemplate) {
                $payoneerTotals[$totalLineId] = $total;
            }
            $payoneerTotalKey = "payoneer_{$totalLineId}";
            try {
                $listTaxAmount = $list->getPayment()->getTaxAmount();
            } catch (ApiExceptionInterface $exception) {
                $listTaxAmount = 0.0;
            }
            if (str_contains((string) $totalLineId, 'tax')) {
                if (!$payoneerTaxAlreadyAdded && $listTaxAmount > 0) {
                    $payoneerTotals = $this->setTaxLine($payoneerTotals, $payoneerTotalKey, $listTaxAmount);
                    $payoneerTaxAlreadyAdded = \true;
                }
            } elseif ($totalLineId === 'order_total') {
                if (!$payoneerTaxAlreadyAdded && $listTaxAmount > 0) {
                    $payoneerTotals = $this->setTaxLine($payoneerTotals, 'payoneer_tax', $listTaxAmount);
                    $payoneerTaxAlreadyAdded = \true;
                }
                try {
                    $listAmount = $list->getPayment()->getAmount();
                } catch (ApiExceptionInterface $exception) {
                    $listAmount = 0.0;
                }
                $payoneerTotals[$payoneerTotalKey] = ['label' => $this->payoneerTotalLine($total['label']), 'value' => wc_price($listAmount)];
            } else {
                $payoneerTotals[$payoneerTotalKey] = ['label' => $this->payoneerTotalLine($total['label']), 'value' => $total['value']];
            }
        }
        return $payoneerTotals;
    }
    protected function payoneerTotalLine(string $value) : string
    {
        return '<span class="payoneer-total">' . $value . '</span>';
    }
    protected function setTaxLine(array $totals, string $taxLineKey, float $taxLineValue) : array
    {
        $totals[$taxLineKey] = ['label' => '<span class="payoneer-total">' . __('Tax:', 'payoneer-checkout') . '</span>', 'value' => wc_price($taxLineValue)];
        return $totals;
    }
}
