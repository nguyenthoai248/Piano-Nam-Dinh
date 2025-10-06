<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ChargeCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Payoneer;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
/**
 * Can create an object representing the Payoneer API.
 */
class PayoneerFactory implements PayoneerFactoryInterface
{
    /** @var ListDeserializerInterface */
    protected $listDeserializer;
    /** @var CustomerSerializerInterface */
    protected $customerSerializer;
    /** @var PaymentSerializerInterface */
    protected $paymentSerializer;
    /** @var CallbackSerializerInterface */
    protected $callbackSerializer;
    /** @var array */
    protected $headers;
    /** @var UpdateListCommandInterface */
    protected $updateListCommand;
    /** @var ChargeCommandInterface */
    protected $chargeCommand;
    /** @var PayoutCommandInterface */
    protected $payoutCommand;
    /** @var string */
    protected $integration;
    /** @var StyleSerializerInterface $styleSerializer */
    protected $styleSerializer;
    /** @var ProductSerializerInterface */
    protected $productSerializer;
    /**
     * @var SystemSerializerInterface
     */
    protected $systemSerializer;
    /**
     * @var CreateListCommandInterface
     */
    protected $createListCommand;
    public function __construct(ListDeserializerInterface $listDeserializer, CustomerSerializerInterface $customerSerializer, PaymentSerializerInterface $paymentSerializer, CallbackSerializerInterface $callbackSerializer, StyleSerializerInterface $styleSerializer, ProductSerializerInterface $productSerializer, SystemSerializerInterface $systemSerializer, array $headers, CreateListCommandInterface $createListCommand, UpdateListCommandInterface $updateListCommand, ChargeCommandInterface $chargeCommand, PayoutCommandInterface $payoutCommand, string $integration)
    {
        $this->listDeserializer = $listDeserializer;
        $this->customerSerializer = $customerSerializer;
        $this->paymentSerializer = $paymentSerializer;
        $this->callbackSerializer = $callbackSerializer;
        $this->styleSerializer = $styleSerializer;
        $this->productSerializer = $productSerializer;
        $this->systemSerializer = $systemSerializer;
        $this->headers = $headers;
        $this->createListCommand = $createListCommand;
        $this->updateListCommand = $updateListCommand;
        $this->chargeCommand = $chargeCommand;
        $this->payoutCommand = $payoutCommand;
        $this->integration = $integration;
    }
    /**
     * @inheritDoc
     */
    public function createPayoneerForApi(ApiClientInterface $apiClient) : PayoneerInterface
    {
        $product = new Payoneer($apiClient, $this->listDeserializer, $this->styleSerializer, $this->headers, $this->createListCommand, $this->updateListCommand, $this->chargeCommand, $this->payoutCommand, $this->customerSerializer, $this->paymentSerializer, $this->callbackSerializer, $this->productSerializer, $this->systemSerializer, $this->integration);
        return $product;
    }
}
