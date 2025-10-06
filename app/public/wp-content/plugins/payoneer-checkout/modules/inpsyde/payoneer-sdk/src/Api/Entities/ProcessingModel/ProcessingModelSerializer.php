<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

class ProcessingModelSerializer implements ProcessingModelSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeProcessingModel(ProcessingModelInterface $processingModel): array
    {
        return ['code' => $processingModel->getCode(), 'type' => $processingModel->getType()];
    }
}
