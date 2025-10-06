<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Collection;

use Syde\Vendor\Psr\Container\ContainerInterface as BaseContainerInterface;
/**
 * Something that can retrieve and determine the existence of a value by key.
 */
interface ContainerInterface extends HasCapableInterface, BaseContainerInterface
{
}
