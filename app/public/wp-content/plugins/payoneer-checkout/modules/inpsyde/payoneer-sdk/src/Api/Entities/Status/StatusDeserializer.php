<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiException;
class StatusDeserializer implements StatusDeserializerInterface
{
    /**
     * @var StatusFactoryInterface Service able to create Status object.
     */
    protected $statusFactory;
    /**
     * @param StatusFactoryInterface $statusFactory
     */
    public function __construct(StatusFactoryInterface $statusFactory)
    {
        $this->statusFactory = $statusFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeStatus(array $statusData): StatusInterface
    {
        if (!isset($statusData['code'])) {
            throw new ApiException('Data contains no expected code element.');
        }
        $code = $statusData['code'];
        if (!isset($statusData['reason'])) {
            throw new ApiException('Data contains no expected reason element.');
        }
        $reason = $statusData['reason'];
        return $this->statusFactory->createStatus($code, $reason);
    }
}
