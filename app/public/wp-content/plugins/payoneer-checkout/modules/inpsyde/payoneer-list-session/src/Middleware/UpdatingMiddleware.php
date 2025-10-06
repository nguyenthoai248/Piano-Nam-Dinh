<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider\HashProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\WcBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class UpdatingMiddleware implements ListSessionProviderMiddleware
{
    use IsProcessingTrait;
    protected bool $isRestRequest;
    /**
     * @var WcBasedUpdateCommandFactoryInterface
     */
    protected $wcBasedListSessionFactory;
    private ListCacheInterface $listCache;
    /**
     * @var HashProviderInterface
     */
    private $hashProvider;
    /**
     * @var string
     */
    private $sessionHashKey;
    protected WcOrderBasedUpdateCommandFactoryInterface $orderBasedUpdateCommandFactory;
    public function __construct(WcBasedUpdateCommandFactoryInterface $wcBasedListSessionFactory, HashProviderInterface $hashProvider, string $sessionHashKey, WcOrderBasedUpdateCommandFactoryInterface $orderBasedUpdateCommandFactory, bool $isRestRequest, ListCacheInterface $listCache)
    {
        $this->wcBasedListSessionFactory = $wcBasedListSessionFactory;
        $this->hashProvider = $hashProvider;
        $this->sessionHashKey = $sessionHashKey;
        $this->orderBasedUpdateCommandFactory = $orderBasedUpdateCommandFactory;
        $this->isRestRequest = $isRestRequest;
        $this->listCache = $listCache;
    }
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        $list = $next->provide($context);
        $longId = $list->getIdentification()->getLongId();
        /**
         * We cache here already to avoid multiple UPDATE calls in
         * case of recursion.
         * After the UPDATE,
         * we will cache again to make sure we cache the updated list.
         */
        $this->listCache->cacheList($list);
        /**
         * If we are already at the payment stage,
         * we will let the gateway deal with final updates
         */
        if ($this->isProcessing()) {
            return $list;
        }
        if ($context->offsetExists('pristine')) {
            //It is a fresh list, nothing to do with it.
            return $list;
        }
        try {
            $order = $context->getOrder();
            if ($order !== null) {
                $list = $this->updateBasedOnOrder($list, $order);
                $this->listCache->cacheList($list);
                return $this->listCache->getCachedListByLongId($longId);
            }
            $customer = $context->getCustomer();
            $session = $context->getSession();
            $cart = $context->getCart();
            if ($session !== null && $cart !== null && $customer !== null) {
                $list = $this->updateBasedOnSession($list, $session, $customer, $cart, $context->offsetExists('pristine'));
            }
        } catch (\Throwable $exception) {
            //TODO Log errors during UPDATE
            $list = $next->provide($context);
        }
        $this->listCache->cacheList($list);
        return $this->listCache->getCachedListByLongId($longId);
    }
    /**
     * @param ListInterface $list
     * @param \WC_Order $order
     *
     * @return ListInterface
     * @throws ApiExceptionInterface
     * @throws CheckoutExceptionInterface
     * @throws CommandExceptionInterface
     */
    protected function updateBasedOnOrder(ListInterface $list, \WC_Order $order): ListInterface
    {
        $command = $this->orderBasedUpdateCommandFactory->createUpdateCommand($order, $list);
        return $this->updateList($command, $list);
    }
    /**
     * @throws \Throwable
     */
    protected function updateBasedOnSession(ListInterface $list, \WC_Session $session, \WC_Customer $customer, \WC_Cart $cart, bool $pristine): ListInterface
    {
        /**
         * We don't want to update List before this hook. It is fired after cart totals is
         * calculated. Before this moment, cart returns 0 for totals and List update will obviously
         * get 'ABORT' because no payment networks support 0 amount.
         */
        if (!$this->isRestRequest && !did_action('woocommerce_after_calculate_totals')) {
            return $list;
        }
        /**
         * Grab the cart hash to check if there have been changes that require an update
         */
        $currentHash = $this->hashProvider->provideHash();
        /**
         * No need to update List if it was created on current request with current context.
         * We write the current hash to prevent an unneeded update next time the LIST is requested
         */
        if ($pristine) {
            $session->set($this->sessionHashKey, $currentHash);
            return $list;
        }
        /**
         * Compare the cart hash.
         * If it has not changed, return the existing LIST
         */
        $storedHash = $session->get($this->sessionHashKey);
        if ($storedHash === $currentHash) {
            return $list;
        }
        $command = $this->wcBasedListSessionFactory->createUpdateCommand($list->getIdentification(), $customer, $cart);
        $updated = $this->updateList($command, $list);
        /**
         * Update checkout hash since the LIST has now changed
         */
        $session->set($this->sessionHashKey, $currentHash);
        return $updated;
    }
    /**
     * @throws CommandExceptionInterface
     */
    protected function updateList(UpdateListCommandInterface $command, ListInterface $list): ListInterface
    {
        do_action('payoneer-checkout.before_update_list', ['longId' => $list->getIdentification()->getLongId(), 'list' => $list]);
        $updatedList = $command->execute();
        do_action('payoneer-checkout.list_session_updated', ['longId' => $updatedList->getIdentification()->getLongId(), 'list' => $updatedList]);
        return $updatedList;
    }
}
