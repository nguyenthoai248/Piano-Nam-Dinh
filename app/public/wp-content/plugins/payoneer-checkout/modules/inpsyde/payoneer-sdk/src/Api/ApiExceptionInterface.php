<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api;

use Syde\Vendor\Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;
/**
 * Should be thrown when API response reports error or API response cannot be parsed.
 */
interface ApiExceptionInterface extends PayoneerSdkExceptionInterface
{
}
