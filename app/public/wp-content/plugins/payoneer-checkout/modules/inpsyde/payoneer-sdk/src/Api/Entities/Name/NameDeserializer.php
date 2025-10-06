<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Name;

class NameDeserializer implements NameDeserializerInterface
{
    /**
     * @var NameFactoryInterface
     */
    protected $nameFactory;
    /**
     * @param NameFactoryInterface $nameFactory To create a Name instance.
     */
    public function __construct(NameFactoryInterface $nameFactory)
    {
        $this->nameFactory = $nameFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeName(array $nameData): NameInterface
    {
        $name = $this->nameFactory->createName($nameData['firstName'], $nameData['lastName']);
        return $name;
    }
}
