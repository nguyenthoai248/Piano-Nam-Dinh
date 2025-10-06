<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway;

use Syde\Vendor\Dhii\Services\Factories\FuncService;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\AssetManager;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Http\Message\UriInterface;
use Stringable;
use WC_Order;
/**
 * The main module class.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 */
class PaymentGatewayModule implements ServiceModule, ExecutableModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    //phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
    /**
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function services() : array
    {
        $moduleRootDir = dirname(__FILE__, 2);
        return (array) (require "{$moduleRootDir}/inc/services.php")();
    }
    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $container) : bool
    {
        /**
         * This module depends on services that it does not provide by itself.
         * If those dependencies are not met, we want to fail as early and loudly as possible.
         * Therefore, we check if the service container contains all expected service IDs
         */
        foreach (['inpsyde_payment_gateway.payment_processor', 'inpsyde_payment_gateway.payment_fields_renderer'] as $key) {
            if (!$container->has($key)) {
                throw new \RuntimeException(sprintf('Service "%1s" is expected to be overridden by other modules', $key));
            }
        }
        add_filter('woocommerce_payment_gateways', static function (array $gateways) use($container) {
            $gateway = $container->get('inpsyde_payment_gateway.gateway');
            $gateways[] = $gateway;
            return $gateways;
        });
        /**
         * Register JS & CSS
         */
        $this->registerAssets($container);
        add_action('woocommerce_init', function () use($container) {
            /** @var callable():void $excludeNotSupportedCountries */
            $excludeNotSupportedCountries = $container->get('inpsyde_payment_gateway.exclude_not_supported_countries');
            $excludeNotSupportedCountries();
            /**
             * By default, only 'pending' and 'failed' order statuses can be cancelled.
             * When returning from an aborted payment (with redirect->challenge->redirect)
             * we do want to be able to cancel our 'on-hold' order though
             */
            add_filter('woocommerce_valid_order_statuses_for_cancel', static function (array $validStatuses, WC_Order $order) {
                $gateway = wc_get_payment_gateway_by_order($order);
                if (!$gateway) {
                    return $validStatuses;
                }
                if (!$gateway instanceof PaymentGateway) {
                    return $validStatuses;
                }
                $validStatuses[] = 'on-hold';
                return $validStatuses;
            }, 10, 2);
            if (is_admin()) {
                $delegate = new FuncService(['inpsyde_payment_gateway.is_live_mode', 'inpsyde_payment_gateway.settings_page_url'], \Closure::fromCallable([$this, 'addSandboxNotice']));
                /** @psalm-suppress MixedFunctionCall */
                $delegate($container)();
            }
        });
        add_action('woocommerce_settings_saved', function () use($container) {
            $delegate = new FuncService(['inpsyde_payment_gateway.is_settings_page', 'inpsyde_payment_gateway.gateway', 'core.http.settings_url'], \Closure::fromCallable([$this, 'reloadSettingsPage']));
            /** @psalm-suppress MixedFunctionCall */
            $delegate($container)();
        });
        add_action('woocommerce_settings_start', function () use($container) {
            $delegate = new FuncService(['inpsyde_payment_gateway.is_settings_page', 'inpsyde_payment_gateway.gateway'], \Closure::fromCallable([$this, 'transferGatewayErrorsAfterReload']));
            /** @psalm-suppress MixedFunctionCall */
            $delegate($container)();
        });
        return \true;
    }
    /**
     * This method is supposed to be called right after saving settings.
     * It could be that one of our field configs depends on another field's value. A good example
     * would be 'is_sandbox' or 'payment_flow'
     * These are read before they're updated, so the page being rendered
     * is based on obsolete information. Here we reload the page after saving the settings
     * so we get to start fresh with correct values.
     *
     * @param bool $isSettingsPage
     * @param PaymentGateway $paymentGateway
     *
     * @return void
     */
    public function reloadSettingsPage(bool $isSettingsPage, PaymentGateway $paymentGateway, UriInterface $settingsUrl) : void
    {
        if (!$isSettingsPage) {
            return;
        }
        $errorParams = [];
        foreach ($paymentGateway->errors as $i => $error) {
            assert(is_string($error));
            $errorParams["error[{$i}]"] = urlencode($error);
        }
        $settingsUrl = add_query_arg($errorParams, $settingsUrl);
        wp_safe_redirect($settingsUrl);
        exit;
    }
    /**
     * When reloading the settings page we serialize validation errors into URL parameters,
     * so they do not get lost. This method fetches them and adds them back to the PaymentGateway
     * @param bool $isSettingsPage
     * @param PaymentGateway $paymentGateway
     *
     * @return void
     */
    public function transferGatewayErrorsAfterReload(bool $isSettingsPage, PaymentGateway $paymentGateway) : void
    {
        if (!$isSettingsPage) {
            return;
        }
        //phpcs:disable WordPress.Security.NonceVerification.Recommended
        //phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $received = wp_unslash($_GET['error'] ?? []);
        assert(is_array($received));
        $errors = [];
        foreach ($received as $error) {
            assert(is_string($error));
            $errors[] = wp_kses_post(urldecode($error));
        }
        //phpcs:enable WordPress.Security.NonceVerification.Recommended
        //phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        /** @psalm-var array<int|string> $errors */
        $paymentGateway->errors = $errors;
    }
    public function addSandboxNotice(bool $liveMode, UriInterface $settingsPageUrl) : void
    {
        if ($liveMode) {
            return;
        }
        add_action('all_admin_notices', static function () use($settingsPageUrl) : void {
            $class = 'notice notice-warning';
            $aTagOpening = sprintf('<a href="%1$s">', (string) $settingsPageUrl);
            $disableTestMode = sprintf(
                /* translators: %1$s, %2$s and %3$s are replaced with the opening and closing 'a' tags */
                esc_html__('%1$sEnable live mode%2$s when you are ready to take live transactions.', 'payoneer-checkout'),
                $aTagOpening,
                '</a>',
                '<a href="">'
            );
            printf('<div class="%1$s"><h4>%2$s</h4><p>%3$s</p></div>', esc_attr($class), esc_html__('Payoneer Checkout Live mode is disabled', 'payoneer-checkout'), wp_kses($disableTestMode, ['a' => ['href' => []]], ['http', 'https']));
        }, 11);
    }
    /**
     * Setup module assets registration.
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function registerAssets(ContainerInterface $container) : void
    {
        add_action(AssetManager::ACTION_SETUP, static function (AssetManager $assetManager) use($container) {
            /** @var Asset[] $assets */
            $assets = $container->get('inpsyde_payment_gateway.assets');
            $assetManager->register(...$assets);
        });
    }
    public function extensions() : array
    {
        static $extensions;
        if ($extensions === null) {
            $extensions = (require_once dirname(__DIR__) . '/inc/extensions.php');
        }
        /** @var callable(): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions();
    }
}
