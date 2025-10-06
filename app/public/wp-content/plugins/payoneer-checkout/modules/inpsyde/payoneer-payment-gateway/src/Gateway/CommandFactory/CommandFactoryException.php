<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayException;
/**
 * Should be thrown when payment processing was failed.
 * Not to be confused with normally processed payment with a negative response.
 */
class CommandFactoryException extends PaymentGatewayException
{
}
