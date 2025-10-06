<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Config;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayException;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
/**
 * Should be thrown when key not found in container.
 */
class NotFoundException extends PaymentGatewayException implements NotFoundExceptionInterface
{
}
