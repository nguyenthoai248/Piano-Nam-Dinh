<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\OrderFinder;

/**
 * Callable class adding support for searching WC orders by Payoneer transaction ID
 * (not to be confused with the _transaction_id field used by WC).
 */
class AddTransactionIdFieldSupport
{
    /**
     * @var string
     */
    protected $transactionIdOrderFieldName;
    /**
     * @param string $transactionIdOrderFieldName
     */
    public function __construct(string $transactionIdOrderFieldName)
    {
        $this->transactionIdOrderFieldName = $transactionIdOrderFieldName;
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
                if (empty($queryVars[$this->transactionIdOrderFieldName])) {
                    return $query;
                }
                $query['meta_query'][] = ['key' => $this->transactionIdOrderFieldName, 'value' => esc_attr($queryVars[$this->transactionIdOrderFieldName])];
                return $query;
            },
            10,
            2
        );
    }
}
