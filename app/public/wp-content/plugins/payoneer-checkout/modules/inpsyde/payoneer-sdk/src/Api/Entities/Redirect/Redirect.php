<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Redirect;

class Redirect implements RedirectInterface
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var array
     */
    private $parameters;
    /**
     * @param string $url A redirection URL.
     * @param string $method A redirection allowed HTTP method.
     * @param string $type A redirection type.
     * @param array $parameters An array of parameters to be sent along with the redirect
     */
    public function __construct(string $url, string $method, string $type, array $parameters)
    {
        $this->url = $url;
        $this->method = $method;
        $this->type = $type;
        $this->parameters = $parameters;
    }
    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }
    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
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
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
