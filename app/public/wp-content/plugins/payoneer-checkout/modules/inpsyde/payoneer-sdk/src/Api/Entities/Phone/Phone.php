<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Phone;

class Phone implements PhoneInterface
{
    /**
     * Mobile phone as a single string.
     *
     * @var string
     */
    protected $unstructuredNumber;
    /**
     * @param string $unstructuredNumber Full phone number.
     */
    public function __construct(string $unstructuredNumber)
    {
        $this->unstructuredNumber = $unstructuredNumber;
    }
    /**
     * @inheritDoc
     */
    public function getUnstructuredNumber(): string
    {
        return $this->unstructuredNumber;
    }
}
