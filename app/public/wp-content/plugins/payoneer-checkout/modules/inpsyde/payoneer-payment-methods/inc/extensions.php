<?php

declare (strict_types=1);
namespace Syde\Vendor;

return static function (): array {
    return ['payoneer_settings.settings_fields' => static function (array $existingFields): array {
        $paymentMethodsFields = require __DIR__ . '/fields.php';
        return \array_merge_recursive($existingFields, $paymentMethodsFields);
    }];
};
