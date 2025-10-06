<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession\OrderBasedListSessionFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;

/**
 *  Provides a List instance by creating it via Payoneer API call using order data retrieved by WC
 * order ID available on the WooCommerce Order Pay page.
 */
class OrderPayListSessionCreator extends FactoryListSessionProvider
{
    /**
     * @var OrderBasedListSessionFactoryInterface
     */
    protected $listSessionFactory;

    /**
     * @var string
     */
    protected $token;
    /**
     * @var int
     */
    protected $order;

    /**
     * @param OrderBasedListSessionFactoryInterface $listSessionFactory
     * @param string $token
     * @param int $order
     */
    public function __construct(
        OrderBasedListSessionFactoryInterface $listSessionFactory,
        string $token,
        int $order
    ) {

        parent::__construct($this->createFactory());
        $this->listSessionFactory = $listSessionFactory;
        $this->token = $token;
        $this->order = $order;
    }

    /**
     * @return callable():ListInterface
     */
    protected function createFactory(): callable
    {
        return function (): ListInterface {
            $order = wc_get_order($this->order);

            if (! $order instanceof WC_Order) {
                throw new CheckoutException(
                    'Failed to get WooCommerce order to create List Session'
                );
            }

            try {
                return $this->listSessionFactory->createList($order, $this->token);
            } catch (FactoryExceptionInterface $exception) {
                throw new CheckoutException('Failed to invoke LIST session factory', 0, $exception);
            }
        };
    }
}
