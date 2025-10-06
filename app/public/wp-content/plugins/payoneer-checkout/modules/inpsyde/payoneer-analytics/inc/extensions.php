<?php

declare (strict_types=1);
namespace Syde\Vendor;

return static function (): array {
    return ['payoneer_settings.settings_fields' => static function (array $previous): array {
        $analyticsFields = ['analytics_fieldset_title' => ['title' => \__('Analytics', 'payoneer-checkout'), 'type' => 'title', 'class' => 'section-payoneer-general'], 'analytics_enabled' => ['title' => \__('Enable analytics', 'payoneer-checkout'), 'type' => 'checkbox', 'default' => 'yes', 'class' => 'section-payoneer-general']];
        return \array_merge($previous, $analyticsFields);
    }];
};
