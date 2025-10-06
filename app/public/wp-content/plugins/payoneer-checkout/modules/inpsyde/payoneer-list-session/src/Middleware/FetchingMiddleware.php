<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\FetchListCommand;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
/**
 * Fetches the list from the external API and stores it in the cache.
 */
class FetchingMiddleware implements ListSessionProviderMiddleware
{
    use IsProcessingTrait;
    protected FetchListCommand $fetchListCommand;
    private const LONG_ID_KEY = '_payoneer-long-id';
    protected ListCacheInterface $listCache;
    private string $selectedPaymentFlow;
    public function __construct(FetchListCommand $fetchListCommand, ListCacheInterface $listCache, string $selectedPaymentFlow)
    {
        $this->fetchListCommand = $fetchListCommand;
        $this->listCache = $listCache;
        $this->selectedPaymentFlow = $selectedPaymentFlow;
    }
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        $longId = $this->getExistingLongId($context);
        if ($longId === null) {
            $list = $next->provide($context);
            $longId = $this->listCache->cacheList($list);
            $this->storeLongId($longId, $context);
            $this->listCache->getCachedListByLongId($longId);
        }
        if (!$this->listCache->hasCachedLongId($longId)) {
            try {
                $list = $this->fetchListCommand->withLongId($longId)->execute();
            } catch (CommandExceptionInterface $exception) {
                $list = $next->provide($context);
            }
            if ($list->getStatus()->getCode() !== 'listed') {
                $list = $next->provide($context);
            }
            $longId = $this->listCache->cacheList($list);
            $this->storeLongId($longId, $context);
        }
        $list = $this->listCache->getCachedListByLongId($longId);
        if ($this->isProcessing() && !$this->validForSelectedFlow($list)) {
            $this->listCache->clear();
            $this->clearExistingLongId($context);
            $list = $next->provide($context);
        }
        return $list;
    }
    private function getExistingLongId(ContextInterface $context): ?string
    {
        $order = $context->getOrder();
        if ($order !== null) {
            /**
             * For orders that have not been paid yet, we check an internal meta key first
             */
            $orderLongId = $order->get_meta(self::LONG_ID_KEY, \true);
            if (!empty($orderLongId)) {
                return (string) $orderLongId;
            }
            /**
             * If that fails, check the actual transaction ID.
             * This scenario is relevant for refunds, where no customer journey and prior LIST exists
             * yet the order has previously been paid.
             *
             * Note that we only read from the transaction ID, but we never update it in here.
             * ...because that would be dangerous.
             */
            $orderTransactionId = $order->get_transaction_id();
            if (!empty($orderTransactionId)) {
                return (string) $orderTransactionId;
            }
        }
        $session = $context->getSession();
        if ($session !== null) {
            $sessionLongId = $session->get(self::LONG_ID_KEY);
            if (is_string($sessionLongId) && !empty($sessionLongId)) {
                return $sessionLongId;
            }
        }
        return null;
    }
    /**
     * Stores the current longId on all possible options
     *
     * @param string $longId
     * @param ContextInterface $context
     *
     * @return void
     */
    private function storeLongId(string $longId, ContextInterface $context): void
    {
        $order = $context->getOrder();
        if ($order !== null) {
            $order->update_meta_data(self::LONG_ID_KEY, $longId);
        }
        $session = $context->getSession();
        if ($session !== null) {
            $session->set(self::LONG_ID_KEY, $longId);
        }
    }
    /**
     * Remove saved longId on all possible options.
     *
     * @param ContextInterface $context
     *
     * @return void
     */
    private function clearExistingLongId(ContextInterface $context): void
    {
        $order = $context->getOrder();
        if ($order !== null) {
            $order->delete_meta_data(self::LONG_ID_KEY);
        }
        $session = $context->getSession();
        if ($session !== null) {
            $session->set(self::LONG_ID_KEY, null);
        }
    }
    protected function validForSelectedFlow(ListInterface $list): bool
    {
        if ($this->selectedPaymentFlow === 'embedded') {
            return \true;
        }
        try {
            $redirect = $list->getRedirect();
        } catch (\Throwable $exception) {
            return \false;
        }
        /**
         * @psalm-suppress RedundantCondition
         *
         * This is redundant now, and we could simply return true here.
         * But in this case, if we ever change our SDK to return null instead of throwing,
         * there will be no visible error, just a bug.
         */
        return $redirect !== null;
    }
}
