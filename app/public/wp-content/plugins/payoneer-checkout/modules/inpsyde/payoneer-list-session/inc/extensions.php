<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\RedirectInjectingListFactory;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactoryInterface;
return static function (): array {
    return ['payoneer_sdk.list_factory' => static function (ListFactoryInterface $previous): ListFactoryInterface {
        return new RedirectInjectingListFactory($previous);
    }];
};
