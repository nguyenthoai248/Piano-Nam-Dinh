<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

class ProcessingModelFactory implements ProcessingModelFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createProcessingModel(string $code, string $type): ProcessingModelInterface
    {
        return new ProcessingModel($code, $type);
    }
}
