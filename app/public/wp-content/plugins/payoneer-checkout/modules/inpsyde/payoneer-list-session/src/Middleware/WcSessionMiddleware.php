<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistorMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\NoopListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;
/**
 * @psalm-type SerializedList=array{
 *              links: array,
 *              identification: array {longId: string, shortId: string, transactionId: string,
 *     pspId?: string}, status: array {code: string, reason: string}, customer?: array, payment:
 *     array {reference: string, amount: float, currency: string}, status: array {code: string,
 *     reason: string},
 * }
 */
class WcSessionMiddleware implements ListSessionPersistorMiddleware, ListSessionProviderMiddleware
{
    /**
     * @var \WC_Session
     */
    protected $wcSession;
    /**
     * @var string
     */
    protected $key;
    /**
     * @var ListSerializerInterface
     */
    protected $serializer;
    /**
     * @var ListDeserializerInterface
     */
    protected $deserializer;
    public function __construct(\WC_Session $wcSession, string $key, ListSerializerInterface $serializer, ListDeserializerInterface $deserializer)
    {
        $this->wcSession = $wcSession;
        $this->key = $key;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }
    public function persist(?ListInterface $list, ContextInterface $context, ListSessionPersistor $next): bool
    {
        if (!$context instanceof CheckoutContext) {
            return $next->persist($list, $context);
        }
        $ours = $list;
        if ($ours) {
            $ours = $this->serializer->serializeListSession($ours);
        }
        $this->wcSession->set($this->key, $ours);
        return $next->persist($list, $context);
    }
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        if (!$context instanceof CheckoutContext) {
            return $next->provide($context);
        }
        /**
         * @psalm-var null|SerializedList
         */
        $serialized = $this->wcSession->get($this->key);
        if (!is_array($serialized)) {
            return $this->persistIfTheListIsNew($next->provide($context), $context);
        }
        try {
            return $this->deserializer->deserializeList($serialized);
        } catch (ApiExceptionInterface $exception) {
            return $this->persistIfTheListIsNew($next->provide($context), $context);
        }
    }
    protected function persistIfTheListIsNew(ListInterface $list, ContextInterface $context): ListInterface
    {
        if (isset($context['list_just_created'])) {
            $this->persist($list, $context, new NoopListSessionPersistor());
        }
        return $list;
    }
}
