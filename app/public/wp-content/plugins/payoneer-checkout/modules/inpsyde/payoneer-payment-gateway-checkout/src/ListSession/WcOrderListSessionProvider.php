<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

class WcOrderListSessionProvider implements OrderAwareListSessionProvider
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var ListDeserializerInterface
     */
    protected $listDeserializer;
    /**
     * @var \WC_Order|null
     */
    protected $order;

    public function __construct(
        string $key,
        ListDeserializerInterface $deserializer,
        \WC_Order $order = null
    ) {

        $this->key = $key;
        $this->listDeserializer = $deserializer;
        $this->order = $order;
    }
    /**
     * @throws CheckoutException
     */
    protected function ensureOrder(): \WC_Order
    {
        if (! $this->order) {
            throw new CheckoutException('No WC_Order configured');
        }

        return $this->order;
    }

    public function provide(): ListInterface
    {
        /** @var string|array{links: array,
         *              identification: array {
         *                  longId: string,
         *                  shortId: string,
         *                  transactionId: string,
         *                  pspId?: string
         *              },
         *              customer?: array,
         *              payment: array,
         *              status: array {
         *                  code: string,
         *                  reason: string
         *              },
         *              redirect?: array {
         *                  url: string,
         *                  method: string,
         *                  type: string
         *              },
         *              division?: string
         * } $listData
         *
         */
        $listData = $this->ensureOrder()->get_meta($this->key, true);
        if (! is_array($listData)) {
            throw new CheckoutException(
                sprintf('Failed to read order meta key "%1$s"', $this->key)
            );
        }
        try {
            $listSession = $this->listDeserializer->deserializeList($listData);
        } catch (ApiExceptionInterface $apiException) {
            throw new CheckoutException(
                sprintf(
                    'Failed to read saved list session data. Exception caught: %1$s',
                    $apiException->getMessage()
                ),
                0,
                $apiException
            );
        }

        return $listSession;
    }

    /**
     * @inheritDoc
     * @param \WC_Order $order
     *
     * @return WcOrderListSessionProvider&static
     */
    public function withOrder(\WC_Order $order): OrderAwareObject
    {
        $new = clone $this;
        $new->order = $order;

        return $new;
    }
}
