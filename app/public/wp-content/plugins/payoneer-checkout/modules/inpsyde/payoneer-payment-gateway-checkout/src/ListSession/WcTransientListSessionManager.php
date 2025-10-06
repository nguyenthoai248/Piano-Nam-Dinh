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
class WcTransientListSessionManager implements ListSessionProvider, ListSessionPersistor, ListSessionRemover
{
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
        string $key,
        ListSerializerInterface $serializer,
        ListDeserializerInterface $deserializer
    ) {

        $this->key = $key;
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
    }
    /**
     * @psalm-suppress UndefinedConstant
     */
    public function persist(ListInterface $list): void
    {
        set_transient(
            $this->key,
            $this->serializer->serializeListSession($list),
            (int)MINUTE_IN_SECONDS * 30
        );
    }

    public function provide(): ListInterface
    {
        /**
         * @psalm-var null|SerializedList $serialized
         */
        $serialized = get_transient($this->key);

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
        delete_transient($this->key);
    }
}
