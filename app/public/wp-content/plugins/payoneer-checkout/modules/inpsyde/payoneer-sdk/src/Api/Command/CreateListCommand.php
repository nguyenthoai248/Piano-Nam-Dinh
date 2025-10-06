<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExecutionException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiCallExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\JsonCodecTrait;
class CreateListCommand extends AbstractPaymentCommand implements CreateListCommandInterface
{
    use DecodeJsonResponseBodyTrait;
    use JsonCodecTrait;
    use PrepareRequestUrlPathTrait;
    /** @var string[] */
    protected $views = [];
    /** @var CustomerSerializerInterface */
    protected $customerSerializer;
    /** @var PaymentSerializerInterface */
    protected $paymentSerializer;
    /** @var CallbackSerializerInterface */
    protected $callbackSerializer;
    /** @var SystemSerializerInterface */
    protected $systemSerializer;
    /**
     * @var string|null
     */
    protected $division;
    /** @var ?CallbackInterface */
    protected $callback;
    /** @var ?string */
    protected $country;
    /** @var ?CustomerInterface */
    protected $customer;
    /** @var ?PaymentInterface */
    protected $payment;
    /**
     * @var ?StyleInterface
     */
    protected $style;
    /**
     * @var ?string
     */
    protected $operationType;
    /**
     * @var ?string
     */
    protected $integrationType;
    /**
     * @var StyleSerializerInterface
     */
    protected $styleSerializer;
    /**
     * @var bool
     */
    protected $allowDelete = \false;
    /**
     * @var ?SystemInterface
     */
    protected $system;
    /**
     * @var ?int
     */
    protected $ttl;
    /**
     * @param array<string, InteractionErrorInterface> $errors
     * @param ApiClientInterface $apiClient
     * @param string $pathTemplate
     * @param ListDeserializerInterface $listDeserializer
     * @param CustomerSerializerInterface $customerSerializer
     * @param PaymentSerializerInterface $paymentSerializer
     * @param CallbackSerializerInterface $callbackSerializer
     * @param ProductSerializerInterface $productSerializer
     * @param StyleSerializerInterface $styleSerializer
     * @param ResponseValidatorInterface $responseValidator
     * @param SystemSerializerInterface $systemSerializer
     * @param string $country
     */
    public function __construct(array $errors, ApiClientInterface $apiClient, string $pathTemplate, ListDeserializerInterface $listDeserializer, CustomerSerializerInterface $customerSerializer, PaymentSerializerInterface $paymentSerializer, CallbackSerializerInterface $callbackSerializer, ProductSerializerInterface $productSerializer, StyleSerializerInterface $styleSerializer, ResponseValidatorInterface $responseValidator, SystemSerializerInterface $systemSerializer, string $country)
    {
        $this->customerSerializer = $customerSerializer;
        $this->paymentSerializer = $paymentSerializer;
        $this->callbackSerializer = $callbackSerializer;
        $this->styleSerializer = $styleSerializer;
        $this->systemSerializer = $systemSerializer;
        $this->country = $country;
        parent::__construct($productSerializer, $apiClient, $listDeserializer, $pathTemplate, $responseValidator, $errors);
    }
    /**
     * @inheritDoc
     */
    public function execute(): ListInterface
    {
        try {
            $this->validateCommandConfiguration();
            $url = 'lists';
            $bodyParams = $this->prepareBodyParams();
            $queryParams = $this->views ? ['views' => $this->views] : [];
            $response = $this->apiClient->post($url, [], $queryParams, $bodyParams);
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
            throw new CommandExecutionException($this, sprintf('Failed to create list session: %1$s.', $exception->getMessage()), 0, $exception);
        } catch (\Throwable $exception) {
            throw new CommandException($this, 'Failed to create list session.', 0, $exception);
        }
    }
    /**
     * @inheritDoc
     */
    public function withViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = $views;
        return $newThis;
    }
    /**
     * @return string[]
     */
    public function getViews(): array
    {
        return $this->views;
    }
    /**
     * @inheritDoc
     */
    public function withAddedViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = array_unique(array_merge($this->views, $views));
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withoutViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = array_diff($this->views, $views);
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withCallback(CallbackInterface $callback): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->callback = $callback;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withCountry(string $country): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->country = $country;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withCustomer(CustomerInterface $customer): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->customer = $customer;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withDivision(string $division): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->division = $division;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withSystem(SystemInterface $system): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->system = $system;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withoutDivision(): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->division = null;
        return $newThis;
    }
    /**
     * @return array
     */
    protected function prepareBodyParams(): array
    {
        $body = ['integration' => $this->integrationType, 'operationType' => $this->operationType, 'division' => $this->division, 'transactionId' => $this->transactionId, 'country' => $this->country, 'products' => array_map([$this->productSerializer, 'serializeProduct'], $this->products), 'allowDelete' => $this->allowDelete];
        $this->customer && $body['customer'] = $this->customerSerializer->serializeCustomer($this->customer);
        $this->payment && $body['payment'] = $this->paymentSerializer->serializePayment($this->payment);
        $this->callback && $body['callback'] = $this->callbackSerializer->serializeCallback($this->callback);
        $this->system && $body['system'] = $this->systemSerializer->serializeSystem($this->system);
        $this->style && $serializedStyle = $this->styleSerializer->serializeStyle($this->style);
        if (isset($serializedStyle) && $serializedStyle) {
            $body['style'] = $serializedStyle;
        }
        if ($this->ttl !== null) {
            $body['ttl'] = $this->ttl;
        }
        return $body;
    }
    /**
     * Check if this command completely configured and ready to be executed. Throw if it doesn't.
     *
     * @throws ApiExceptionInterface If the command is not configured.
     *
     * @psalm-assert string $this->longId,
     * @psalm-assert string $this->transactionId
     * @psalm-assert CustomerInterface $this->customer
     * @psalm-assert PaymentInterface $this->payment
     * @psalm-assert CallbackInterface $this->callback
     */
    protected function validateCommandConfiguration(): void
    {
        $errors = [];
        $requiredFields = ['integration' => $this->integrationType, 'operationType' => $this->operationType, 'transactionId' => $this->transactionId, 'division' => $this->division, 'customer' => $this->customer, 'country' => $this->country, 'callback' => $this->callback, 'style' => $this->style, 'system' => $this->system];
        if ($this->operationType !== 'UPDATE') {
            $requiredFields['payment'] = $this->payment;
        }
        foreach ($requiredFields as $fieldName => $validationSubject) {
            if ($validationSubject === null) {
                $errors[] = sprintf('%1$s must be set before create command can be executed', $fieldName);
            }
        }
        if ($errors) {
            throw new CommandException($this, $this->prepareValidationFailedMessage($errors), 0, null);
        }
    }
    /**
     * Prepare a message about this command validation failure.
     *
     * @param string[] $errors List of found errors descriptions.
     *
     * @return string Prepared message.
     */
    protected function prepareValidationFailedMessage(array $errors): string
    {
        $baseMessage = 'CreateListCommand validation failed. Errors found: ';
        return $baseMessage . implode(';' . \PHP_EOL, $errors);
    }
    public function withStyle(StyleInterface $style): CreateListCommandInterface
    {
        $newThis = clone $this;
        $newThis->style = $style;
        return $newThis;
    }
    public function withOperationType(string $operationType): CreateListCommandInterface
    {
        $newThis = clone $this;
        $newThis->operationType = $operationType;
        return $newThis;
    }
    public function withIntegrationType(string $integrationType): CreateListCommandInterface
    {
        $newThis = clone $this;
        $newThis->integrationType = $integrationType;
        return $newThis;
    }
    public function withAllowDelete(bool $allowDelete): CreateListCommandInterface
    {
        $newThis = clone $this;
        $newThis->allowDelete = $allowDelete;
        return $newThis;
    }
    /**
     * @inheritDoc
     */
    public function withTtl(int $ttl): CreateListCommandInterface
    {
        $newThis = clone $this;
        $newThis->ttl = $ttl;
        return $newThis;
    }
    public function getDivision(): ?string
    {
        return $this->division;
    }
    public function getCallback(): ?CallbackInterface
    {
        return $this->callback;
    }
    public function getCountry(): ?string
    {
        return $this->country;
    }
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }
    public function getStyle(): ?StyleInterface
    {
        return $this->style;
    }
    public function getOperationType(): ?string
    {
        return $this->operationType;
    }
    public function getIntegrationType(): ?string
    {
        return $this->integrationType;
    }
    public function isAllowDelete(): bool
    {
        return $this->allowDelete;
    }
    public function getSystem(): ?SystemInterface
    {
        return $this->system;
    }
    public function getTtl(): ?int
    {
        return $this->ttl;
    }
}
