<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\Factory\ListSession\WcBasedListSessionFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Cart;
use WC_Customer;

/**
 * Provides a List instance by creating it via Payoneer API call using cart and customer obects.
 * Intended to be used on checkout.
 */
class CheckoutListSessionCreator extends FactoryListSessionProvider
{
    /**
     * @var WcBasedListSessionFactoryInterface
     */
    private $wcBasedListSessionFactory;
    /**
     * @var \WC_Customer
     */
    protected $customer;
    /**
     * @var \WC_Cart
     */
    protected $cart;
    /**
     * @var string
     */
    protected $listSecurityToken;

    public function __construct(
        WcBasedListSessionFactoryInterface $wcBasedListSessionFactory,
        WC_Customer $customer,
        WC_Cart $cart,
        string $listSecurityToken
    ) {

        $this->wcBasedListSessionFactory = $wcBasedListSessionFactory;
        $this->cart = $cart;
        $this->customer = $customer;
        $this->listSecurityToken = $listSecurityToken;
        parent::__construct($this->createFactory());
    }

    /**
     * @return callable():ListInterface
     */
    protected function createFactory(): callable
    {
        return function (): ListInterface {
            return $this->wcBasedListSessionFactory->createList(
                $this->customer,
                $this->cart,
                $this->listSecurityToken
            );
        };
    }
}
