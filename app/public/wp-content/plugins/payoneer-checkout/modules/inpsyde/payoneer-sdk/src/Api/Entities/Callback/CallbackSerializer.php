<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderSerializerInterface;
class CallbackSerializer implements CallbackSerializerInterface
{
    /**
     * @var HeaderSerializerInterface
     */
    protected $headerSerializer;
    /**
     * @param HeaderSerializerInterface $headerSerializer To convert headers to arrays.
     */
    public function __construct(HeaderSerializerInterface $headerSerializer)
    {
        $this->headerSerializer = $headerSerializer;
    }
    /**
     * @inheritDoc
     */
    public function serializeCallback(CallbackInterface $callback): array
    {
        $serializedCallback = ['returnUrl' => $callback->getReturnUrl(), 'cancelUrl' => $callback->getCancelUrl()];
        $serializedCallback['summaryUrl'] = $callback->getSummaryUrl();
        try {
            $serializedCallback['notificationUrl'] = $callback->getNotificationUrl();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }
        $headers = $callback->getNotificationHeaders();
        $serializedHeaders = array_map([$this->headerSerializer, 'serializeHeader'], $headers);
        $serializedCallback['notificationHeaders'] = $serializedHeaders;
        return $serializedCallback;
    }
}
