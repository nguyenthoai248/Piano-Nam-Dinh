<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

trait PrepareRequestUrlPathTrait
{
    /**
     * @var ?string
     */
    protected $longId;
    /**
     * @var string
     */
    protected $pathTemplate;
    public function prepareRequestUrlPath(): string
    {
        assert(is_string($this->longId));
        return sprintf($this->pathTemplate, $this->longId);
    }
}
