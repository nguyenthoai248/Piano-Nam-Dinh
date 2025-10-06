<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderDeserializerInterface;
class CallbackDeserializer implements CallbackDeserializerInterface
{
    /**
     * @var CallbackFactoryInterface A service able to create a new callback instance.
     */
    protected $callbackFactory;
    /**
     * @var HeaderDeserializerInterface
     */
    protected $headerDeserializer;
    /**
     * @param CallbackFactoryInterface $callbackFactory
     * @param HeaderDeserializerInterface $headerDeserializer
     */
    public function __construct(CallbackFactoryInterface $callbackFactory, HeaderDeserializerInterface $headerDeserializer)
    {
        $this->callbackFactory = $callbackFactory;
        $this->headerDeserializer = $headerDeserializer;
    }
    /**
     * @inheritDoc
     */
    public function deserializeCallback(array $callbackData): CallbackInterface
    {
        if (!$callbackData['returnUrl']) {
            throw new ApiException('Data contains no expected returnUrl element.');
        }
        $returnUrl = $callbackData['returnUrl'];
        if (!$callbackData['cancelUrl']) {
            throw new ApiException('Data contains no expected cancelUrl element.');
        }
        $cancelUrl = $callbackData['cancelUrl'];
        if (!$callbackData['summaryUrl']) {
            throw new ApiException('Data contains no expected summaryUrl element.');
        }
        $summaryUrl = $callbackData['summaryUrl'];
        $notificationUrl = $callbackData['notificationUrl'] ?? '';
        $notificationHeadersData = $callbackData['notificationHeaders'];
        $notificationHeaders = array_map([$this->headerDeserializer, 'deserializeHeader'], $notificationHeadersData);
        return $this->callbackFactory->createCallback($returnUrl, $summaryUrl, $cancelUrl, $notificationUrl, $notificationHeaders);
    }
}
