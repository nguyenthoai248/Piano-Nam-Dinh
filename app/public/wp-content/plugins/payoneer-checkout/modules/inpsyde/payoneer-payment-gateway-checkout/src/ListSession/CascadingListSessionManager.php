<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

/**
 * Iterates over a list of providers and returns the first successful result
 */
class CascadingListSessionManager implements ListSessionProvider, ListSessionRemover, ListSessionPersistor, OrderAwareObject
{
    /**
     * @var list<ListSessionProvider|ListSessionRemover|ListSessionPersistor>
     */
    protected $children;

    /**
     * @param list<ListSessionProvider|ListSessionRemover|ListSessionPersistor> $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }

    public function provide(): ListInterface
    {
        $lastException = new CheckoutException('No providers configured for ' . __CLASS__);
        foreach ($this->children as $child) {
            if (! $child instanceof ListSessionProvider) {
                continue;
            }
            try {
                return $child->provide();
            } catch (CheckoutExceptionInterface $exception) {
                $lastException = $exception;
            }
        }
        throw $lastException;
    }

    public function clear(): void
    {
        foreach ($this->children as $child) {
            if (! $child instanceof ListSessionRemover) {
                continue;
            }
            $child->clear();
        }
    }

    public function persist(ListInterface $list): void
    {
        foreach ($this->children as $child) {
            if (! $child instanceof ListSessionPersistor) {
                continue;
            }
            $child->persist($list);
        }
    }

    public function withOrder(\WC_Order $order): OrderAwareObject
    {
        foreach ($this->children as $key => $child) {
            if (! $child instanceof OrderAwareObject) {
                continue;
            }
            $this->children[$key] = $child->withOrder($order);
        }

        return $this;
    }
}
