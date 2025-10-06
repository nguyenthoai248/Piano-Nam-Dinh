<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory(['checkout.flow_options', 'checkout.flow_options_description'], static function (array $paymentFlowOptions, string $paymentFlowDescription): array {
    return ['payment_flow' => ['title' => \__('Select payment flow', 'payoneer-checkout'), 'type' => 'select', 'description' => $paymentFlowDescription, 'options' => $paymentFlowOptions, 'default' => 'embedded', 'class' => 'section-payoneer-general']];
});
