<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

/**
 * Decorates another ListSessionProvider and replicates its behaviour - including Exceptions
 * Implements Remover and Persistor
 */
class CachingListSessionManager implements OrderAwareListSessionProvider, ListSessionRemover, OrderAwareListSessionPersistor
{
    /**
     * @var ListSessionProvider
     */
    protected $inner;

    /**
     * @var ListInterface|CheckoutExceptionInterface|null
     */
    protected $listOrException = null;

    /**
     * @psalm-param ListSessionProvider $inner
     */
    public function __construct(object $inner)
    {
        $this->inner = $inner;
    }

    public function provide(): ListInterface
    {
        if (! $this->listOrException) {
            try {
                $this->listOrException = $this->inner->provide();
            } catch (CheckoutExceptionInterface $exception) {
                $this->listOrException = $exception;
            }
        }
        if ($this->listOrException instanceof CheckoutExceptionInterface) {
            throw $this->listOrException;
        }

        return $this->listOrException;
    }

    public function clear(): void
    {
        $this->listOrException = null;
        if ($this->inner instanceof ListSessionRemover) {
            $this->inner->clear();
        }
    }

    public function persist(ListInterface $list): void
    {
        $this->listOrException = $list;
        if ($this->inner instanceof ListSessionPersistor) {
            $this->inner->persist($list);
        }
    }

    public function withOrder(\WC_Order $order): OrderAwareObject
    {
        if ($this->inner instanceof OrderAwareObject) {
            $this->listOrException = null;
            $this->inner = $this->inner->withOrder($order);
        }
        return $this;
    }
}
