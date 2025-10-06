<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

class PhoneDeserializer implements PhoneDeserializerInterface
{
    /**
     * A service able to create phone.
     *
     * @var PhoneFactoryInterface
     */
    protected $phoneFactory;
    /**
     * @param PhoneFactoryInterface $phoneFactory To create Phone instance.
     */
    public function __construct(PhoneFactoryInterface $phoneFactory)
    {
        $this->phoneFactory = $phoneFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializePhone(array $phoneData): PhoneInterface
    {
        $phone = $this->phoneFactory->createPhone($phoneData['unstructuredNumber']);
        return $phone;
    }
}
