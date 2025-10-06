<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

interface ProcessingModelFactoryInterface
{
    /**
     * Create a new ProcessingModel instance
     *
     * @param string $code
     * @param string $type
     *
     * @return ProcessingModelInterface
     */
    public function createProcessingModel(string $code, string $type): ProcessingModelInterface;
}
