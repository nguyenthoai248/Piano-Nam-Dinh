<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

class ProcessingModel implements ProcessingModelInterface
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $type;
    public function __construct(string $code, string $type)
    {
        $this->code = $code;
        $this->type = $type;
    }
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }
}
