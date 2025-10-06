<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Config;

use InvalidArgumentException;
interface StylesConfig
{
    /**
     * @throws InvalidArgumentException
     */
    public static function title(string $value): string;
    /**
     * @throws InvalidArgumentException
     */
    public static function description(string $value): string;
}
