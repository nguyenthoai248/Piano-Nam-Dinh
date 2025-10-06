<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\FetchListCommand;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
/**
 * Ensures that a local LIST is actually usable by performing a GET call to OPG
 */
class ValidatingMiddleware implements ListSessionProviderMiddleware
{
    protected FetchListCommand $fetchListCommand;
    private ListSessionPersistor $persistor;
    private array $longIds = [];
    public function __construct(FetchListCommand $fetchListCommand, ListSessionPersistor $persistor)
    {
        $this->fetchListCommand = $fetchListCommand;
        $this->persistor = $persistor;
    }
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        $list = $next->provide($context);
        $longId = $list->getIdentification()->getLongId();
        /**
         * Only validate once per longId per request
         */
        if (in_array($longId, $this->longIds, \true)) {
            return $list;
        }
        if ($context->offsetExists('list_just_created')) {
            /**
             * It is a fresh list, nothing to do with it.
             */
            $this->longIds[] = $longId;
            $this->persistor->persist($list, $context);
            return $list;
        }
        try {
            $this->longIds[] = $longId;
            $list = $this->fetchListCommand->withLongId($longId)->execute();
        } catch (CommandExceptionInterface $exception) {
            /**
             * Wipe everything we have stored in this path
             */
            $this->persistor->persist(null, $context);
            /**
             * We assume that this middleware is executed very early in the chain
             * in order to assess the validity of everything that comes after it.
             *
             * Therefore, just calling $next() in case of failure seems safe and also avoids recursion
             */
            $list = $next->provide($context);
            $this->persistor->persist($list, $context);
        }
        return $list;
    }
}
