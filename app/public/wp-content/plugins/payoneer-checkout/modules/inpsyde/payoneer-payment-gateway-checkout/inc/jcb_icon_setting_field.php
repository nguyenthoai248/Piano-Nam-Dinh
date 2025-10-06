<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory([], static function () {
    return ['show_jcb_icon' => ['title' => \__('JCB logo', 'payoneer-checkout'), 'type' => 'checkbox', 'label' => \__('Display JCB logo next to the payment method title on the checkout page. Merchants of Record shall not display the logo.', 'payoneer-checkout'), 'default' => 'no']];
});
