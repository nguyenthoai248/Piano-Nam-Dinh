<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\ChargeCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializerInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
class Payoneer implements PayoneerInterface
{
    use DecodeJsonResponseBodyTrait;
    /**
     * @var ListDeserializerInterface Service able to create a List instance from array.
     */
    protected $listDeserializer;
    /**
     * @var ApiClientInterface Service able to send API requests.
     */
    protected $apiClient;
    /**
     * @var CreateListCommandInterface
     */
    protected $listCommand;
    /**
     * @var UpdateListCommandInterface A command updating a session.
     */
    protected $updateCommand;
    /**
     * @var ChargeCommandInterface A command making charge for a session.
     */
    protected $chargeCommand;
    /**
     * @var PayoutCommandInterface
     */
    protected $payoutCommand;
    /**
     * @var string
     */
    protected $integration;
    /**
     * @var CustomerSerializerInterface
     */
    protected $customerSerializer;
    /**
     * @var PaymentSerializerInterface
     */
    protected $paymentSerializer;
    /**
     * @var CallbackSerializerInterface
     */
    protected $callbackSerializer;
    /**
     * @var StyleSerializerInterface
     */
    protected $styleSerializer;
    /**
     * @var ProductSerializerInterface
     */
    protected $productSerializer;
    /**
     * @var array
     */
    protected $headers;
    /**
     * @var SystemSerializerInterface
     */
    protected $systemSerializer;
    /**
     * @param ApiClientInterface $apiClient
     * @param ListDeserializerInterface $listDeserializer
     * @param StyleSerializerInterface $styleSerializer
     * @param array $headers
     * @param CreateListCommandInterface $listCommand
     * @param UpdateListCommandInterface $updateCommand
     * @param ChargeCommandInterface $chargeCommand
     * @param PayoutCommandInterface $payoutCommand
     * @param CustomerSerializerInterface $customerSerializer
     * @param PaymentSerializerInterface $paymentSerializer
     * @param CallbackSerializerInterface $callbackSerializer
     * @param ProductSerializerInterface $productSerializer
     * @param SystemSerializerInterface $systemSerializer
     * @param string $integration
     */
    public function __construct(ApiClientInterface $apiClient, ListDeserializerInterface $listDeserializer, StyleSerializerInterface $styleSerializer, array $headers, CreateListCommandInterface $listCommand, UpdateListCommandInterface $updateCommand, ChargeCommandInterface $chargeCommand, PayoutCommandInterface $payoutCommand, CustomerSerializerInterface $customerSerializer, PaymentSerializerInterface $paymentSerializer, CallbackSerializerInterface $callbackSerializer, ProductSerializerInterface $productSerializer, SystemSerializerInterface $systemSerializer, string $integration)
    {
        $this->apiClient = $apiClient;
        $this->listDeserializer = $listDeserializer;
        $this->listCommand = $listCommand;
        $this->updateCommand = $updateCommand;
        $this->chargeCommand = $chargeCommand;
        $this->payoutCommand = $payoutCommand;
        $this->customerSerializer = $customerSerializer;
        $this->paymentSerializer = $paymentSerializer;
        $this->callbackSerializer = $callbackSerializer;
        $this->integration = $integration;
        $this->styleSerializer = $styleSerializer;
        $this->productSerializer = $productSerializer;
        $this->headers = $headers;
        $this->systemSerializer = $systemSerializer;
    }
    /**
     * Quick&dirty solution to leaking sensitive data into logs via exceptions
     * Works for now, might have to be re-assessed later.
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArrayAssignment
     * @param array $payload
     *
     * @return array
     */
    protected function redactSensitiveData(array $payload): array
    {
        $redacted = '*****';
        $payload['customer']['email'] && $payload['customer']['email'] = $redacted;
        $payload['customer']['phones'] && $payload['customer']['phones'] = [$redacted => []];
        isset($payload['customer']['registration']['id']) && $payload['customer']['registration']['id'] = $redacted;
        isset($payload['customer']['addresses']['billing']['street']) && $payload['customer']['addresses']['billing']['street'] = $redacted;
        isset($payload['customer']['addresses']['billing']['name']['firstName']) && $payload['customer']['addresses']['billing']['name']['firstName'] = $redacted;
        isset($payload['customer']['addresses']['billing']['name']['lastName']) && $payload['customer']['addresses']['billing']['name']['lastName'] = $redacted;
        isset($payload['customer']['addresses']['shipping']['street']) && $payload['customer']['addresses']['shipping']['street'] = $redacted;
        isset($payload['customer']['addresses']['shipping']['name']['firstName']) && $payload['customer']['addresses']['shipping']['name']['firstName'] = $redacted;
        isset($payload['customer']['addresses']['shipping']['name']['lastName']) && $payload['customer']['addresses']['shipping']['name']['lastName'] = $redacted;
        isset($payload['customer']['name']['firstName']) && $payload['customer']['name']['firstName'] = $redacted;
        isset($payload['customer']['name']['lastName']) && $payload['customer']['name']['lastName'] = $redacted;
        return $payload;
    }
    /**
     * @inheritDoc
     */
    public function getListCommand(): CreateListCommandInterface
    {
        return $this->listCommand;
    }
    /**
     * @inheritDoc
     */
    public function getUpdateCommand(): UpdateListCommandInterface
    {
        return $this->updateCommand;
    }
    /**
     * @inheritDoc
     */
    public function getChargeCommand(): ChargeCommandInterface
    {
        return $this->chargeCommand;
    }
    /**
     * @inheritDoc
     */
    public function getPayoutCommand(): PayoutCommandInterface
    {
        return $this->payoutCommand;
    }
}
