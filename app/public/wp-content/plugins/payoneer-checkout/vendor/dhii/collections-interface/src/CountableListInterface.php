<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Collection;

use Traversable;
use Countable;
/**
 * A list that can be counted.
 *
 * @since 0.2
 */
interface CountableListInterface extends Traversable, Countable
{
}
