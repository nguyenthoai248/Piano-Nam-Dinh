<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;

class WcOrderListSessionPersistor implements OrderAwareListSessionPersistor
{
    /**
     * @var string
     */
    protected $listSessionFieldName;
    /**
     * @var ListSerializerInterface
     */
    protected $serializer;
    /**
     * @var \WC_Order|null
     */
    protected $order;

    public function __construct(
        string $listSessionFieldName,
        ListSerializerInterface $serializer,
        \WC_Order $order = null
    ) {

        $this->listSessionFieldName = $listSessionFieldName;
        $this->serializer = $serializer;
        $this->order = $order;
    }

    public function persist(ListInterface $list): void
    {
        $order = $this->ensureOrder();
        $order->update_meta_data($this->listSessionFieldName, $this->serializer->serializeListSession($list));
        $order->save();
    }

    /**
     * @throws CheckoutException
     */
    private function ensureOrder(): \WC_Order
    {
        if (!$this->order) {
            throw new CheckoutException('No WC_Order configured');
        }

        return $this->order;
    }

    /**
     * @inheritDoc
     * @param \WC_Order $order
     *
     * @return WcOrderListSessionPersistor&static
     */
    public function withOrder(\WC_Order $order): OrderAwareObject
    {
        $new = clone $this;
        $new->order = $order;

        return $new;
    }
}
