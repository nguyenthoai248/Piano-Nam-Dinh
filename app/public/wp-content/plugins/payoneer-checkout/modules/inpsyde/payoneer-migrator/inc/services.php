<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Migration\Migrator;
return static function (): array {
    return ['migration.migrator' => new Constructor(Migrator::class), 'migration.plugin_version_option_name' => new Value('payoneer_plugin_version')];
};
