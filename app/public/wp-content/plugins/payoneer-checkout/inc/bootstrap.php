<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Inpsyde\Modularity\Module\Module;
use Syde\Vendor\Inpsyde\Modularity\Package;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\PayoneerProperties;
return static function (string $mainPluginFile, callable $onError, Module ...$modules): Package {
    $autoload = \dirname($mainPluginFile) . '/vendor/autoload.php';
    if (\is_readable($autoload)) {
        include_once $autoload;
    }
    $properties = PayoneerProperties::new($mainPluginFile);
    $package = Package::new($properties);
    \add_action($package->hookName(Package::ACTION_FAILED_BOOT), $onError);
    /**
     * WP 6.7.0 changed the way textdomains are loaded.
     * Calling load_plugin_textdomain too early now produces a notice.
     * However, the just-in-time-loading early only checks wp-content/languages by default.
     * So we currently do not have a safe way to expose our plugin path,
     * resulting in potentially missing translations.
     * We're keeping both paths, hoping there are going to be amendments for this in future releases
     *
     * First, we force our plugin languages path into the textdomain loading system.
     */
    \add_filter('lang_dir_for_domain', static function ($dir, $domain) {
        if ($dir !== \false || $domain !== 'payoneer-checkout') {
            return $dir;
        }
        return \WP_PLUGIN_DIR . '/payoneer-checkout/languages/';
    }, 10, 3);
    /**
     * Now we expose our custom path safely.
     */
    \add_action('init', fn() => \load_plugin_textdomain('payoneer-checkout'));
    foreach ($modules as $module) {
        $package->addModule($module);
    }
    $package->boot();
    return $package;
};
