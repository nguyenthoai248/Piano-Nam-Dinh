<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\System;

class System implements SystemInterface
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $version;
    /**
     * @param string $type
     * @param string $code
     * @param string $version
     */
    public function __construct(string $type, string $code, string $version)
    {
        $this->type = $type;
        $this->code = $code;
        $this->version = $version;
    }
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
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
    public function getVersion(): string
    {
        return $this->version;
    }
}
