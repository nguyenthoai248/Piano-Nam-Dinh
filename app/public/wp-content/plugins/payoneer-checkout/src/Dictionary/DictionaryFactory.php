<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Dictionary;

use Syde\Vendor\Psr\Container\ContainerInterface;
class DictionaryFactory extends \Syde\Vendor\Dhii\Container\DictionaryFactory
{
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $data): ContainerInterface
    {
        return new Dictionary($data);
    }
}
