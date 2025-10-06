<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
/**
 * This is a payment-related command, that is aware of payment itself and products this payment is for.
 */
abstract class AbstractPaymentCommand extends AbstractCommand implements PaymentCommandInterface
{
    /**
     * @var ProductSerializerInterface
     */
    protected $productSerializer;
    /** @var ?PaymentInterface */
    protected $payment;
    /** @var ProductInterface[] */
    protected $products = [];
    /**
     * @param ProductSerializerInterface $productSerializer
     * @param ApiClientInterface $apiClient
     * @param ListDeserializerInterface $listDeserializer
     * @param string $pathTemplate
     * @param ResponseValidatorInterface $responseValidator
     * @param array<string, InteractionErrorInterface> $errors
     */
    public function __construct(ProductSerializerInterface $productSerializer, ApiClientInterface $apiClient, ListDeserializerInterface $listDeserializer, string $pathTemplate, ResponseValidatorInterface $responseValidator, array $errors)
    {
        $this->productSerializer = $productSerializer;
        $this->apiClient = $apiClient;
        parent::__construct($apiClient, $listDeserializer, $pathTemplate, $responseValidator, $errors);
    }
    /**
     * @inheritDoc
     */
    public function withTransactionId(string $transactionId): PaymentCommandInterface
    {
        $newThis = clone $this;
        $newThis->transactionId = $transactionId;
        return $newThis;
    }
    /**
     * Return a new instance with provided products.
     *
     * @param ProductInterface[] $products
     *
     * @return static
     */
    public function withProducts(array $products): PaymentCommandInterface
    {
        $newThis = clone $this;
        $newThis->products = $products;
        return $newThis;
    }
    /**
     * Return a new instance with provided payment.
     *
     * @param PaymentInterface $payment
     *
     * @return static
     */
    public function withPayment(PaymentInterface $payment): PaymentCommandInterface
    {
        $newThis = clone $this;
        $newThis->payment = $payment;
        return $newThis;
    }
    /**
     * @return array[]
     *
     * @psalm-return list<array>
     */
    protected function prepareProducts(): array
    {
        $serializedProducts = [];
        foreach ($this->products as $product) {
            $serializedProducts[] = $this->productSerializer->serializeProduct($product);
        }
        return $serializedProducts;
    }
    /**
     * @return PaymentInterface|null
     */
    public function getPayment(): ?PaymentInterface
    {
        return $this->payment;
    }
    /**
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
