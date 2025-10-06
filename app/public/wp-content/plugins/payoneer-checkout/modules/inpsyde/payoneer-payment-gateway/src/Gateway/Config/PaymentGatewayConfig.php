<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Config;

use Syde\Vendor\Psr\Container\ContainerInterface;
class PaymentGatewayConfig implements ContainerInterface
{
    /**
     * Payment gateway configuration
     *
     * @var array<string, mixed>
     */
    protected $config;
    /**
     * @param array<string, mixed> $config Payment gateway configuration.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (isset($this->config[$id])) {
            return $this->config[$id];
        }
        throw new NotFoundException('%1$s not found in Gateway config container.');
    }
    /**
     * @inheritDoc
     */
    public function has($id) : bool
    {
        return isset($this->config[$id]);
    }
}
