<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header;

class Header implements HeaderInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $value;
    /**
     * @param string $name Header name.
     * @param string $value Header value.
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
