<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistorMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;
/**
 * @psalm-import-type SerializedList from WcSessionMiddleware
 */
class WcOrderMiddleware implements ListSessionPersistorMiddleware, ListSessionProviderMiddleware
{
    /**
     * @var string
     */
    protected $metaKey;
    /**
     * @var ListSerializerInterface
     */
    protected $serializer;
    /**
     * @var ListDeserializerInterface
     */
    private $deserializer;
    public function __construct(string $metaKey, ListSerializerInterface $serializer, ListDeserializerInterface $deserializer)
    {
        $this->metaKey = $metaKey;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }
    public function persist(?ListInterface $list, ContextInterface $context, ListSessionPersistor $next): bool
    {
        if (!$context instanceof PaymentContext) {
            return $next->persist($list, $context);
        }
        $order = $context->getOrder();
        $order->update_meta_data($this->metaKey, $this->serializeList($list));
        $order->save();
        return $next->persist($list, $context);
    }
    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        if (!$context instanceof PaymentContext) {
            return $next->provide($context);
        }
        $order = $context->getOrder();
        /**
         * @psalm-var null|SerializedList $serialized
         */
        $serialized = $order->get_meta($this->metaKey, \true);
        if (!is_array($serialized)) {
            return $next->provide($context);
        }
        try {
            return $this->deserializer->deserializeList($serialized);
        } catch (ApiExceptionInterface $exception) {
            return $next->provide($context);
        }
    }
    private function serializeList(?ListInterface $list): array
    {
        if ($list) {
            return $this->serializer->serializeListSession($list);
        }
        return [];
    }
}
