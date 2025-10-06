<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Permission\SystemReportPermissionHandler;
return new Factory([], static function (): array {
    return ['section_system_report' => [
        'id' => 'section_system_report',
        'type' => 'title',
        /* translators: Title of the settings section */
        'title' => \__('System Report', 'payoneer-checkout'),
        'class' => 'section-payoneer-general',
    ], SystemReportPermissionHandler::OPTION_FEATURE_OPT_IN_KEY => [
        'id' => 'payoneer_system_report_opt_in',
        /* translators: Name of the opt-in setting (displayed on left side) */
        'title' => \__('Automated Troubleshooting', 'payoneer-checkout'),
        /* translators: Label of the opt-in field (displayed next to the checkbox) */
        'label' => \__('Enable', 'payoneer-checkout'),
        'type' => 'checkbox',
        /* translators: Description of the opt-in setting (displayed below the checkbox) */
        'description' => \__('When "Enable" is checked, Payoneer support can remotely collect system information to help resolve issues, if they arise, without interrupting you.<br/>When "Enable" is unchecked, Payoneer support will need to request information manually from you, which could slow down the troubleshooting process and might delay problem resolution.<br/>We therefore recommend to keep "Enable" checked.', 'payoneer-checkout'),
        'default' => 'yes',
        'class' => 'section-payoneer-general',
    ]];
});
