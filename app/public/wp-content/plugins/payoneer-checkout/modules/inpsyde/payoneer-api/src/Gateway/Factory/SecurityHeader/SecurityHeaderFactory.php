<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\Gateway\Factory\SecurityHeader;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;
class SecurityHeaderFactory implements SecurityHeaderFactoryInterface
{
    /**
     * @var HeaderFactoryInterface
     */
    protected $headerFactory;
    /**
     * @var string
     */
    protected $securityHeaderName;
    public function __construct(HeaderFactoryInterface $headerFactory, string $securityHeaderName)
    {
        $this->headerFactory = $headerFactory;
        $this->securityHeaderName = $securityHeaderName;
    }
    /**
     * @inheritDoc
     */
    public function createSecurityHeader(string $headerValue): HeaderInterface
    {
        return $this->headerFactory->createHeader($this->securityHeaderName, $headerValue);
    }
}
