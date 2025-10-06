<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\ListSession;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutException;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;

/**
 * @psalm-type SerializedList=array{
 *              links: array,
 *              identification: array {longId: string, shortId: string, transactionId: string, pspId?: string},
 *              status: array {code: string, reason: string},
 *              customer?: array,
 *              payment: array {reference: string, amount: float, currency: string},
 *              status: array {code: string, reason: string},
 * }
 */
class WcSessionListSessionManager implements ListSessionProvider, ListSessionPersistor, ListSessionRemover
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

    public function __construct(
        \WC_Session $wcSession,
        string $key,
        ListSerializerInterface $serializer,
        ListDeserializerInterface $deserializer
    ) {

        $this->wcSession = $wcSession;
        $this->key = $key;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }

    public function persist(ListInterface $list): void
    {
        $this->wcSession->set($this->key, $this->serializer->serializeListSession($list));
    }

    public function provide(): ListInterface
    {
        /**
         * @psalm-var null|SerializedList
         */
        $serialized = $this->wcSession->get($this->key);

        if (! is_array($serialized)) {
            throw new CheckoutException(
                'List session not found.'
            );
        }

        try {
            return $this->deserializer->deserializeList($serialized);
        } catch (ApiExceptionInterface $exception) {
            throw new CheckoutException(
                'Failed to create LIST session from saved data.'
            );
        }
    }

    /**
     * @inerhitDoc
     */
    public function clear(): void
    {
        $this->wcSession->set($this->key, null);
    }

    /**
     * Return a new instance using a different storage key
     *
     * @param string $key
     *
     * @return $this
     */
    public function withKey(string $key): self
    {
        $new = clone $this;
        $new->key = $key;
        return $new;
    }
}
