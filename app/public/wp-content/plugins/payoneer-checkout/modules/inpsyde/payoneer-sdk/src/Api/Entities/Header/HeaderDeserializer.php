<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

class HeaderDeserializer implements HeaderDeserializerInterface
{
    /**
     * @var HeaderFactoryInterface
     */
    protected $headerFactory;
    /**
     * @param HeaderFactoryInterface $headerFactory To create a Header instance.
     */
    public function __construct(HeaderFactoryInterface $headerFactory)
    {
        $this->headerFactory = $headerFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeHeader(array $headerData): HeaderInterface
    {
        return $this->headerFactory->createHeader($headerData['name'], $headerData['value']);
    }
}
