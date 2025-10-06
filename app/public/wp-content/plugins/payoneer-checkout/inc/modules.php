<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Inpsyde\Logger\LoggerModule;
use Syde\Vendor\Inpsyde\Modularity\Module\Module;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Api\ApiModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\CoreModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\EmbeddedPaymentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Environment\EnvironmentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\HostedPaymentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSessionModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Migration\MigrationModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\PaymentMethodsModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\SettingsModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ThirdPartyCompat\ThirdPartyCompatModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\WebhooksModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\WebSdkModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\WpModule;
use Syde\Vendor\Inpsyde\PayoneerSdk\SdkModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\StatusReportModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AdminBanner\AdminBannerModule;
return static function (): iterable {
    $modules = [new EnvironmentModule(), new WpModule(), new PageDetectorModule(), new LoggerModule(), new StatusReportModule(), new SdkModule(), new CoreModule(), new Inpsyde\PaymentGateway\PaymentGatewayModule(), new PaymentMethodsModule(), new ListSessionModule(), new HostedPaymentModule(), new CheckoutModule(), new EmbeddedPaymentModule(), new WebhooksModule(), new WebSdkModule(), new MigrationModule(), new ThirdPartyCompatModule(), new AdminBannerModule(), new AnalyticsModule(), new ApiModule(), new SettingsModule()];
    return $modules;
};
