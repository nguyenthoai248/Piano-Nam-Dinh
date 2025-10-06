<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Container;

use Syde\Vendor\Dhii\Collection\WritableMapFactoryInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * @inheritDoc
 */
class DictionaryFactory implements WritableMapFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $data): ContainerInterface
    {
        return new Dictionary($data);
    }
}
