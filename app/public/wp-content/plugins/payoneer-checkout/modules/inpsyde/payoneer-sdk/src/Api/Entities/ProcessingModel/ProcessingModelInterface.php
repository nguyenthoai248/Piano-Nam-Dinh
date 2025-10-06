<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

interface ProcessingModelInterface
{
    /**
     * Return processing model code
     *
     * @return string
     */
    public function getCode(): string;
    /**
     * Return processing model type
     *
     * @return string
     */
    public function getType(): string;
}
