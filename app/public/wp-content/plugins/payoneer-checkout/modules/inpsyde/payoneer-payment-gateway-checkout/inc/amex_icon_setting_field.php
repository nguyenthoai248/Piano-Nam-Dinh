<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory([], static function () {
    return ['show_amex_icon' => ['title' => \__('American Express logo', 'payoneer-checkout'), 'type' => 'checkbox', 'label' => \__('Display American Express logo next to the payment method title on the checkout page.', 'payoneer-checkout'), 'default' => 'yes']];
});
