<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment;

use Syde\Vendor\Dhii\Services\Factory;
return static function (): array {
    return ['hosted_payment.is_enabled' => new Factory(['checkout.selected_payment_flow'], static function (string $configuredFlow): bool {
        return $configuredFlow === 'hosted';
    })];
};
