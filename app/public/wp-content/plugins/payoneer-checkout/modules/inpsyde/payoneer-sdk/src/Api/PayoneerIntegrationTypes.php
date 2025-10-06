<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api;

/**
 * Enumerates possible Payoneer integration types.
 */
class PayoneerIntegrationTypes
{
    public const DISPLAY_NATIVE = 'DISPLAY_NATIVE';
    public const PURE_NATIVE = 'PURE_NATIVE';
    public const HOSTED = 'HOSTED';
    public const SELECTIVE_NATIVE = 'SELECTIVE_NATIVE';
    public const MOBILE_NATIVE = 'MOBILE_NATIVE';
    public const EMBEDDED = 'EMBEDDED';
}
