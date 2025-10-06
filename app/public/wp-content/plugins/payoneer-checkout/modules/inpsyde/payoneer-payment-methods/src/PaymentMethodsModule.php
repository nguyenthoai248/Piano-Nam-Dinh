<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods;

use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PaymentGateway\Method\PaymentMethodDefinition;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentMethodServiceProviderTrait;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\AbstractPayoneerPaymentMethodDefinition;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Affirm;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\AfterPay;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Bancontact;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\CreditCards;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Eps;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\HostedPaymentPage;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Ideal;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Klarna;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\Multibanco;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Definition\P24;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\AsyncRefundAdminUi;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin\AsyncRefundGlobalNotices;
use Syde\Vendor\Psr\Container\ContainerInterface;
use WC_Order;
use WC_Order_Refund;
class PaymentMethodsModule implements ServiceModule, ExtendingModule, ExecutableModule
{
    use ModuleClassNameIdTrait;
    use PaymentMethodServiceProviderTrait;
    /**
     * @var AbstractPayoneerPaymentMethodDefinition[]
     */
    private array $paymentMethods = [];
    public function __construct()
    {
        $this->paymentMethods[] = new HostedPaymentPage();
        $this->paymentMethods[] = new CreditCards();
        $this->paymentMethods[] = new AfterPay();
        $this->paymentMethods[] = new Klarna();
        $this->paymentMethods[] = new Affirm();
        $this->paymentMethods[] = new Bancontact();
        $this->paymentMethods[] = new Eps();
        $this->paymentMethods[] = new Ideal();
        $this->paymentMethods[] = new Multibanco();
        $this->paymentMethods[] = new P24();
    }
    public function run(ContainerInterface $container): bool
    {
        add_action('woocommerce_init', function () use ($container) {
            /** @var callable():void $excludeNotSupportedCountries */
            $excludeNotSupportedCountries = $container->get('payment_methods.exclude_not_supported_countries');
            $excludeNotSupportedCountries();
            /** @var string[] $payoneerGateways */
            $payoneerGateways = (array) $container->get('payment_gateways');
            $this->allowCancelingOnHoldOrders($payoneerGateways);
        });
        //        $this->setPaymentGatewaysEnabledStatus($container);
        /**
         * This hook fires directly before the RefundProcessor::refundOrderPayment() handler is
         * invoked and provides a reference to the full WC_Order_Refund object.
         */
        add_action('woocommerce_create_refund', static function ($refund, $args) use ($container) {
            if (!$refund instanceof WC_Order_Refund || !is_array($args)) {
                return;
            }
            $processor = $container->get('payment_methods.common.refund_processor');
            assert($processor instanceof RefundProcessor);
            $processor->attemptEarlyRefund($refund, $args);
        }, 10, 2);
        $this->setupAsyncRefundUi($container);
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function services(): array
    {
        static $services;
        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }
        /** @var array<string, callable(ContainerInterface $container):mixed> $paymentMethodServices*/
        $paymentMethodServices = $this->providePaymentMethodServices(...$this->paymentMethods);
        /** @var callable(): array<string, callable(ContainerInterface $container):mixed> $services */
        return array_merge(['payment_methods.all' => function (): array {
            return array_map(static function (PaymentMethodDefinition $method): string {
                return $method->id();
            }, $this->paymentMethods);
        }], $services(), $paymentMethodServices);
    }
    public function getMethodDefinition(string $fqcn): PaymentMethodDefinition
    {
        foreach ($this->paymentMethods as $method) {
            if ($method instanceof $fqcn) {
                return $method;
            }
        }
        throw new \Exception("No payment method found with FQCN: {$fqcn}");
    }
    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        return ['payment_gateways' => function (array $gateways): array {
            foreach ($this->paymentMethods as $paymentMethod) {
                $gateways[] = $paymentMethod->id();
            }
            return $gateways;
        }, 'payoneer-settings.settings-tabs' => function (array $tabs): array {
            foreach ($this->paymentMethods as $paymentMethod) {
                /**
                 * fallbackTitle is used intentionally instead of title.
                 *
                 * title contains payment method name entered by user, and we
                 * want to use original payment method title for the tab name.
                 */
                $tabs[$paymentMethod->id()] = $paymentMethod->fallbackTitle();
            }
            return $tabs;
        }, 'payoneer_settings.settings_fields' => function (array $fields): array {
            foreach ($this->paymentMethods as $paymentMethod) {
                $sectionKey = 'section-' . $paymentMethod->id();
                $titleFieldKey = 'title-' . $paymentMethod->id();
                $titleField = ['title' => __('Title', 'payoneer-checkout'), 'type' => 'text', 'description' => __('The title that customers see at checkout', 'payoneer-checkout'), 'default' => $paymentMethod->fallbackTitle(), 'desc_tip' => \true, 'class' => $sectionKey];
                $fields[$titleFieldKey] = $titleField;
                if ($paymentMethod->id() !== 'payoneer-hosted') {
                    continue;
                }
                $descriptionFieldKey = 'description-' . $paymentMethod->id();
                $descriptionField = $this->provideDescriptionField($sectionKey);
                $fields[$descriptionFieldKey] = $descriptionField;
            }
            return $fields;
        }];
    }
    /**
     *  By default, only 'pending' and 'failed' order statuses can be cancelled.
     *  When returning from an aborted payment (with redirect->challenge->redirect)
     *  we do want to be able to cancel our 'on-hold' order though
     *
     * @param string[] $payoneerPaymentGateways
     */
    protected function allowCancelingOnHoldOrders(array $payoneerPaymentGateways): void
    {
        add_filter('woocommerce_valid_order_statuses_for_cancel', static function (array $validStatuses, WC_Order $order) use ($payoneerPaymentGateways): array {
            if (!in_array($order->get_payment_method(), $payoneerPaymentGateways, \true)) {
                return $validStatuses;
            }
            $validStatuses[] = 'on-hold';
            return $validStatuses;
        }, 10, 2);
    }
    protected function provideDescriptionField(string $sectionKey): array
    {
        /* translators: Text used as a default Hosted payment method description which is displayed on checkout in hosted mode and in HPP fallback case in embedded mode */
        $defaultDescription = __('Payment will be done on a dedicated payment page', 'payoneer-checkout');
        return ['title' => __('Description', 'payoneer-checkout'), 'type' => 'text', 'description' => __('The description that customers see at checkout', 'payoneer-checkout'), 'default' => $defaultDescription, 'desc_tip' => \true, 'class' => $sectionKey];
    }
    private function setupAsyncRefundUi(ContainerInterface $container): void
    {
        $orderDetails = $container->get('wp.refund.admin.async_refund_order_ui');
        assert($orderDetails instanceof AsyncRefundAdminUi);
        $globalNotices = $container->get('wp.refund.admin.async_refund_global_notices');
        assert($globalNotices instanceof AsyncRefundGlobalNotices);
        $orderDetails->init();
        $globalNotices->init();
    }
}
