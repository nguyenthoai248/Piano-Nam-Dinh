<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\RefundFinder;

/**
 * Callable class adding support for searching WC order refunds by Payoneer Payout ID.
 */
class AddPayoutIdFieldSupport
{
    /**
     * @var string
     */
    protected $payoutIdFieldName;
    /**
     * @param string $payoutIdFieldName
     */
    public function __construct(string $payoutIdFieldName)
    {
        $this->payoutIdFieldName = $payoutIdFieldName;
    }
    public function __invoke(): void
    {
        add_filter(
            'woocommerce_order_data_store_cpt_get_orders_query',
            /**
             * @param array{meta_query: array<array-key, mixed>} $query
             * @param array<array-key, string> $queryVars
             *
             * @return array
             */
            function (array $query, array $queryVars): array {
                if (empty($queryVars[$this->payoutIdFieldName])) {
                    return $query;
                }
                $query['meta_query'][] = ['key' => $this->payoutIdFieldName, 'value' => esc_attr($queryVars[$this->payoutIdFieldName])];
                return $query;
            },
            10,
            2
        );
    }
}
