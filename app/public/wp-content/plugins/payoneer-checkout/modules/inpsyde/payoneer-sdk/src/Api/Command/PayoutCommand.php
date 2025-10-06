<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExecutionException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiCallExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\JsonCodecTrait;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
/**
 * @psalm-import-type PaymentType from PaymentSerializerInterface
 */
class PayoutCommand extends AbstractPaymentCommand implements PayoutCommandInterface
{
    use DecodeJsonResponseBodyTrait;
    use JsonCodecTrait;
    use PrepareRequestUrlPathTrait;
    /** @var PaymentSerializerInterface */
    protected $paymentSerializer;
    /**
     * @param array<string, InteractionErrorInterface> $errors
     * @param ApiClientInterface $apiClient To make API calls.
     * @param string $pathTemplate To prepare API request URL.
     * @param ListDeserializerInterface $listDeserializer To prepare LIST from API response.
     * @param PaymentSerializerInterface $paymentSerializer
     * @param ResponseValidatorInterface $responseValidator
     * @param ProductSerializerInterface $productSerializer
     */
    public function __construct(array $errors, ApiClientInterface $apiClient, string $pathTemplate, ListDeserializerInterface $listDeserializer, PaymentSerializerInterface $paymentSerializer, ResponseValidatorInterface $responseValidator, ProductSerializerInterface $productSerializer)
    {
        $this->paymentSerializer = $paymentSerializer;
        parent::__construct($productSerializer, $apiClient, $listDeserializer, $pathTemplate, $responseValidator, $errors);
    }
    /**
     * @inheritDoc
     */
    public function execute(): ListInterface
    {
        if (!$this->longId) {
            throw new CommandException($this, 'The longId field must be set before PayoutCommand can be executed.', 0, null);
        }
        $urlPath = $this->prepareRequestUrlPath();
        $bodyParams = $this->prepareBodyParams();
        try {
            $response = $this->apiClient->post($urlPath, [], [], $bodyParams);
            $this->onResponse($response);
            $parsedBody = $this->decodeJsonResponseBody($response);
            return $this->listDeserializer->deserializeList($parsedBody);
        } catch (ApiCallExceptionInterface $exception) {
            /**
             * If the client was able to receive a response (but with an error status),
             * we still want to inspect the interaction codes in order to throw a dedicated
             * exception.
             */
            $response = $exception->getResponse();
            if ($response !== null) {
                $this->onResponse($response);
            }
            /**
             * If we cannot find anything wrong in the response body, throw a generic exception
             */
            throw new CommandExecutionException($this, sprintf('Failed to initiate payout for LIST %1$s: %2$s.', $this->longId, $exception->getMessage()), 0, $exception);
        } catch (\Throwable $exception) {
            throw new CommandException($this, sprintf('Failed to do payout (refund) for LIST %1$s', $this->longId), 0, null);
        }
    }
    /**
     * @psalm-return array{transactionId?: string, payment?: PaymentType}
     */
    protected function prepareBodyParams(): array
    {
        $params = [];
        if (is_string($this->transactionId)) {
            $params['transactionId'] = $this->transactionId;
        }
        if ($this->payment instanceof PaymentInterface) {
            $params['payment'] = $this->paymentSerializer->serializePayment($this->payment);
        }
        return $params;
    }
    /**
     * @inheritDoc
     *
     * @return ListAwareCommandInterface&static
     */
    public function withLongId(string $longId): ListAwareCommandInterface
    {
        $newThis = clone $this;
        $newThis->longId = $longId;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function getLongId(): ?string
    {
        return $this->longId;
    }
}
