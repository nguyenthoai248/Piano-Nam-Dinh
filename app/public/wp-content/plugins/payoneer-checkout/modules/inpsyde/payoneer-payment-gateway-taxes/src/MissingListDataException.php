<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\Exception\PayoneerExceptionInterface;
use RangeException;
class MissingListDataException extends RangeException implements PayoneerExceptionInterface
{
}
