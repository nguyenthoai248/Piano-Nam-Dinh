<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

class ProcessingModelDeserializer implements ProcessingModelDeserializerInterface
{
    /**
     * @var ProcessingModelFactoryInterface
     */
    protected $processingModelFactory;
    /**
     * @param ProcessingModelFactoryInterface $processingModelFactory
     */
    public function __construct(ProcessingModelFactoryInterface $processingModelFactory)
    {
        $this->processingModelFactory = $processingModelFactory;
    }
    /**
     * @inheritDoc
     */
    public function deserializeProcessingModel(array $processingModelData): ProcessingModelInterface
    {
        return $this->processingModelFactory->createProcessingModel($processingModelData['code'], $processingModelData['type']);
    }
}
