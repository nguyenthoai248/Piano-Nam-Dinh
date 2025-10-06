<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes;

use Exception;
use Syde\Vendor\Inpsyde\Assets\Asset;
use Syde\Vendor\Inpsyde\Assets\AssetManager;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\Product\WcCartBasedProductListFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\AbstractTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\DiscountTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\OrderFeeTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\OrderLineItemTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\OrderShippingTaxModifier;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\TaxModifierItem;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use Syde\Vendor\Psr\Container\ContainerInterface;
use WC_Data_Exception;
use WC_Order;
use WC_Session;
class TaxesModule implements ServiceModule, ExecutableModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    public const TAX_RATE_ID = "1";
    /**
     * @var bool $isEmailTemplate
     */
    protected $isEmailTemplate = \false;
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $services;
    /**
     * @var array<string, callable>
     * @psalm-var array<string,
     *     callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed>
     */
    protected $extensions;
    public function __construct()
    {
        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "{$moduleRootDir}/inc/services.php")();
        $this->extensions = (require "{$moduleRootDir}/inc/extensions.php")();
    }
    public function run(ContainerInterface $container) : bool
    {
        $this->registerAssets($container);
        $this->registerTaxModifiers($container);
        $this->registerPayForOrderPayoneerLines($container);
        $this->registerPayForOrderPayoneerModifiers($container);
        $this->registerPayoneerTaxRateForMOR($container);
        $this->registerOrderStatusChangeActions($container);
        $this->registerAbortedPaymentBehaviour($container);
        add_action('woocommerce_before_calculate_totals', function () use($container) : void {
            if ($container->get('wc.is_checkout') && !$container->get('wp.is_ajax')) {
                add_filter('wc_tax_enabled', [$this, 'logNoticeIfTaxesAreDisabled']);
            }
        });
        return \true;
    }
    public function extensions() : array
    {
        return $this->extensions;
    }
    public function services() : array
    {
        return $this->services;
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
            $assets = $container->get('taxes.assets');
            $assetManager->register(...$assets);
        });
    }
    protected function registerTaxModifiers(ContainerInterface $container) : void
    {
        add_action('woocommerce_after_calculate_totals', function () use($container) {
            if (!$container->get('wc.is_checkout')) {
                return;
            }
            /** @var TaxConfigurationChecker $taxConfigurationChecker */
            $taxConfigurationChecker = $container->get('inpsyde_payment_gateway.tax_configuration_checker');
            if (!$taxConfigurationChecker->taxCanBeApplied() || !$container->get('wc.is_checkout')) {
                return;
            }
            try {
                /** @var ListSessionManager $session */
                $session = $container->get('list_session.manager');
                $list = $session->provide(new CheckoutContext());
                if (!$this->isMorList($list)) {
                    return;
                }
                $products = $list->getProducts();
            } catch (CheckoutExceptionInterface $checkoutException) {
                return;
            }
            $modifiers = $this->getConfiguredTaxModifiers($products, $container);
            try {
                foreach ($modifiers as $modifier) {
                    $modifier->modify();
                }
            } catch (\Throwable $exception) {
                do_action('payoneer-checkout.invalid_tax_configuration', ['message' => $exception->getMessage()]);
                return;
            }
            $cart = $container->get('wc.cart');
            assert($cart instanceof \WC_Cart);
            try {
                $this->validateCartAmounts($cart, $list);
            } catch (ListAndDisplayedAmountsMismatchException $exception) {
                do_action('payoneer-checkout.invalid_tax_configuration', ['message' => $exception->getMessage()]);
            }
            do_action('payoneer-checkout.cart_taxes_modified', $list, $cart, ...$modifiers);
        }, 990);
    }
    /**
     * Provides tax modifiers configured with provided products
     *
     * @param ProductInterface[] $products
     * @param ContainerInterface $container
     *
     * @return AbstractTaxModifier[]
     */
    protected function getConfiguredTaxModifiers(array $products, ContainerInterface $container) : array
    {
        /** @var AbstractTaxModifier $lineItemTaxModifier */
        $lineItemTaxModifier = $container->get('inpsyde_payment_gateway.line_item_tax_modifier');
        /** @var AbstractTaxModifier $shippingTaxModifier */
        $shippingTaxModifier = $container->get('inpsyde_payment_gateway.shipping_tax_modifier');
        /** @var AbstractTaxModifier $feeTaxModifier */
        $feeTaxModifier = $container->get('inpsyde_payment_gateway.fee_tax_modifier');
        /** @var DiscountTaxModifier $discountTaxModifier */
        $discountTaxModifier = $container->get('inpsyde_payment_gateway.discount_tax_modifier');
        foreach ($products as $product) {
            $modifier = new TaxModifierItem($product->getCode(), $product->getTaxAmount());
            if ($product->getType() === ProductType::OTHER) {
                $feeTaxModifier->push($modifier);
            } elseif ($modifier->getProductId() === WcCartBasedProductListFactory::SHIPPING_SERVICES_TYPE) {
                $shippingTaxModifier->push($modifier);
            } elseif ($modifier->getTaxAmount() < 0) {
                $discountTaxModifier->push($modifier);
            } else {
                $lineItemTaxModifier->push($modifier);
            }
        }
        return [$lineItemTaxModifier, $shippingTaxModifier, $feeTaxModifier, $discountTaxModifier];
    }
    private function currentPaymentMethodIsPayoneer() : bool
    {
        /** @var WC_Session | null $session */
        $session = WC()->session;
        if (is_null($session)) {
            return \false;
        }
        $userOnFrontendIsPayoneer = !is_admin() && $session->get('chosen_payment_method') === 'payoneer-checkout';
        // We only read the data $_POST
        // phpcs:ignore
        $userOnPayIsPayoneer = isset($_POST['action']) && $_POST['action'] === 'payoneer_order_pay';
        // phpcs:ignore
        $userRecalculatePayoneer = $this->isRecalculatePayoneer($_POST);
        return $userOnFrontendIsPayoneer || $userOnPayIsPayoneer || $userRecalculatePayoneer;
    }
    private function currentOrderIsPayoneer(\WC_Order $order) : bool
    {
        return $order->get_payment_method() === 'payoneer-checkout';
    }
    protected function isRecalculatePayoneer(array $attr) : bool
    {
        if (!is_admin()) {
            return \false;
        }
        if (empty($attr['action']) || !in_array($attr['action'], ['woocommerce_calc_line_taxes', 'woocommerce_save_order_items'])) {
            return \false;
        }
        if (empty($attr['order_id'])) {
            return \false;
        }
        if (!is_numeric($attr['order_id'])) {
            return \false;
        }
        $orderId = $attr['order_id'];
        $order = wc_get_order($orderId);
        if (!is_a($order, WC_Order::class)) {
            return \false;
        }
        return $this->currentOrderIsPayoneer($order);
    }
    public function registerPayForOrderPayoneerLines(ContainerInterface $container) : void
    {
        /**
         * There is no easy way to check if we are currently inside the email template to execute
         * email-specific code. To accomplish this, we hook into the
         * 'woocommerce_email_order_details' email template hook, which runs exclusively within
         * the email template. At a low priority, we enable the 'isEmailTemplate' variable,
         * and at a high priority, we disable it. Totals are rendered between these priorities.
         * When we execute our line items code (woocommerce_get_order_item_totals), we can
         * distinguish between the email template and the WooCommerce page.
         */
        add_action('woocommerce_email_order_details', function () {
            $this->isEmailTemplate = \true;
        }, 0);
        add_action('woocommerce_email_order_details', function () {
            $this->isEmailTemplate = \false;
        }, 999);
        /**
         * The goal of this section of the code is to render duplicated total line items separately
         * for the Payoneer payment method and other methods. The line items are then hidden with
         * CSS and toggled with JS. There is one exception: this section of the code is also called
         * from email templates. It is crucial to ensure that line items are not duplicated when
         * rendering email templates. To achieve this, we need to check the 'isEmailTemplate'
         * variable, which was set by the preceding two blocks of code, and execute the
         * code accordingly.
         */
        add_filter('woocommerce_get_order_item_totals', function (array $totals, WC_Order $order) use($container) {
            if ($this->isEmailTemplate && $order->get_payment_method() !== 'payoneer-checkout' || !$container->get('wc.is_checkout_pay_page') && !$this->isEmailTemplate) {
                return $totals;
            }
            $listSessionManager = $container->get('list_session.manager');
            assert($listSessionManager instanceof ListSessionProvider);
            try {
                $list = $listSessionManager->provide(new PaymentContext($order));
            } catch (\Throwable $exception) {
                return $totals;
            }
            if (!$this->isMorList($list)) {
                return $totals;
            }
            return (new PayForOrderTotalsManager())($totals, $list, $this->isEmailTemplate);
        }, 990, 2);
    }
    /**
     * @throws WC_Data_Exception
     */
    public function orderPayoneerModifiers(\WC_Order $order, ContainerInterface $container) : void
    {
        if ($order->get_payment_method() !== 'payoneer-checkout' || $container->get('wc.settings.price_include_tax')) {
            return;
        }
        /** @var string $listSessionFieldName */
        $listSessionFieldName = $container->get('inpsyde_payment_gateway.list_session_field_name');
        $orderLineItemTaxModifier = new OrderLineItemTaxModifier($order);
        $orderShippingTaxModifier = new OrderShippingTaxModifier($order);
        $orderFeeTaxModifier = new OrderFeeTaxModifier($order);
        /** @var array{
         *              products: array {
         *                   code: string,
         *                   taxAmount: string
         *              },
         *              payment: array {
         *                    amount: string,
         *               }
         * } $payoneerList
         *
         */
        $payoneerList = $order->get_meta($listSessionFieldName);
        $products = $payoneerList['products'];
        /** @var array{type:string,code:string,taxAmount:string,name:string} $product */
        foreach ($products as $product) {
            $modifier = new TaxModifierItem($product['code'], (float) $product['taxAmount']);
            if ($product['type'] === ProductType::OTHER) {
                if (!empty($product['name'])) {
                    $modifier->setProductId(sanitize_title($product['name']));
                    $orderFeeTaxModifier->push($modifier);
                } else {
                    throw new MissingListDataException('List item shipping is missing parameter: name.');
                }
            } elseif ($product['type'] === ProductType::SERVICE) {
                $orderShippingTaxModifier->push($modifier);
            } else {
                $orderLineItemTaxModifier->push($modifier);
            }
        }
        $orderLineItemTaxModifier->modify();
        $orderShippingTaxModifier->modify();
        $orderFeeTaxModifier->modify();
        if (!empty($payoneerList['payment']['amount'])) {
            $order->set_total($payoneerList['payment']['amount']);
        }
        $order->update_taxes();
        $order->save();
    }
    public function registerAbortedPaymentBehaviour(ContainerInterface $container) : void
    {
        add_action('payoneer-checkout.payment_aborted', static function (\WC_Order $order) : void {
            $calculateTaxArgs = ['country' => $order->get_billing_country(), 'state' => $order->get_billing_state(), 'postcode' => $order->get_billing_postcode(), 'city' => $order->get_billing_city()];
            $order->calculate_taxes($calculateTaxArgs);
            $order->calculate_totals(\false);
        }, 990);
        add_filter('wc_get_template', function ($template, $templateName, $args) {
            /**
             * When the order fails taxes calculation is skipped. Consequently, some parameters
             * have incorrect values. Forcing totals to recalculate, fixes that issue.
             * wc_get_template hook is used, because thank you page actions don't provide
             * WC_Order instance, but just the ID, therefore we can't manipulate order object.
             * With wc_get_template that is not the case. We can access original order object.
             */
            if ($templateName !== 'checkout/thankyou.php') {
                return $template;
            }
            if (!isset($args['order']) || !$args['order'] instanceof WC_Order) {
                return $template;
            }
            $order = $args['order'];
            if (!is_a($order, \WC_Order::class)) {
                return $template;
            }
            if (!$this->currentOrderIsPayoneer($order)) {
                return $template;
            }
            $order->calculate_totals();
            return $template;
        }, 10, 3);
    }
    public function registerOrderStatusChangeActions(ContainerInterface $container) : void
    {
        add_action(
            'woocommerce_order_status_changed',
            /**
             * @throws WC_Data_Exception
             */
            function (int $orderId, string $_oldStatus, string $newStatus) use($container) : void {
                if (in_array($newStatus, ['processing', 'completed'])) {
                    $order = wc_get_order($orderId);
                    if (!is_a($order, WC_Order::class)) {
                        return;
                    }
                    $this->orderPayoneerModifiers($order, $container);
                }
            },
            990,
            3
        );
    }
    public function registerPayForOrderPayoneerModifiers(ContainerInterface $container) : void
    {
        add_action(
            'woocommerce_order_after_calculate_totals',
            /**
             * @param bool $_andTaxes
             * @param WC_Order $order
             * @throws WC_Data_Exception
             */
            function (bool $_andTaxes, \WC_Abstract_Order $order) use($container) : void {
                if (!is_a($order, WC_Order::class)) {
                    return;
                }
                // phpcs:ignore
                if (isset($_POST['action']) && in_array($_POST['action'], ['editpost', 'woocommerce_refund_line_items'])) {
                    return;
                }
                if ($this->isPaymentAborted()) {
                    return;
                }
                $this->orderPayoneerModifiers($order, $container);
            },
            990,
            2
        );
    }
    protected function registerPayoneerTaxRateForMOR(ContainerInterface $container) : void
    {
        add_filter('woocommerce_find_rates', function (array $rates) use($container) {
            if (!$this->currentPaymentMethodIsPayoneer() || $this->isPaymentAborted() || $container->get('wc.settings.price_include_tax')) {
                return $rates;
            }
            $session = $container->get('wc.session');
            if (!is_a($session, WC_Session::class)) {
                return $rates;
            }
            $customer = $session->get('customer');
            if (empty($customer)) {
                return $rates;
            }
            /** @var ListSessionManager $listSessionManager */
            $listSessionManager = $container->get('list_session.manager');
            try {
                $list = $listSessionManager->provide(new CheckoutContext());
            } catch (\Throwable $exception) {
                return $rates;
            }
            if (!$this->isMorList($list)) {
                return $rates;
            }
            // If Payoneer payment method is selected, we always return a fake tax rate which is used for further calculations.
            return [self::TAX_RATE_ID => ['rate' => 0, 'label' => __('Tax', 'payoneer-checkout'), 'shipping' => 'yes', 'compound' => 'yes']];
        });
        add_filter('woocommerce_rate_code', function (string $code) {
            if ($code === '' && $this->currentPaymentMethodIsPayoneer()) {
                return 'PAYONEER-US-TAX-' . self::TAX_RATE_ID;
            }
            return $code;
        });
    }
    /**
     * Check whether List uses MoR processing model.
     *
     * @param ListInterface $list
     * @return bool
     */
    protected function isMorList(ListInterface $list) : bool
    {
        try {
            $processingModel = $list->getProcessingModel();
        } catch (ApiExceptionInterface $exception) {
            return \false;
        }
        return $processingModel->getType() === 'MOR';
    }
    protected function isPaymentAborted() : bool
    {
        return isset($_GET['interactionCode']) && $_GET['interactionCode'] === 'ABORT';
    }
    /**
     * Throw if cart total or tax amount doesn't match total or tax amount from the List.
     *
     * @param \WC_Cart $cart
     * @param ListInterface $list
     *
     * @throws ApiExceptionInterface
     */
    protected function validateCartAmounts(\WC_Cart $cart, ListInterface $list) : void
    {
        $payment = $list->getPayment();
        if ($payment->getAmount() !== (float) $cart->get_total('')) {
            throw new ListAndDisplayedAmountsMismatchException(sprintf('Cart amount %1$f doesn\'t match List payment amount %2$f.', $cart->get_total(''), $payment->getAmount()));
        }
        if ($payment->getTaxAmount() !== (float) $cart->get_taxes_total()) {
            throw new ListAndDisplayedAmountsMismatchException(sprintf('Cart tax amount %1$f doesn\'t match List payment amount %2$f.', $cart->get_total_tax(), $payment->getTaxAmount()));
        }
    }
    public function logNoticeIfTaxesAreDisabled(bool $taxesEnabled) : bool
    {
        if (!did_action('payoneer-checkout.taxes_not_enabled') && !$taxesEnabled) {
            do_action('payoneer-checkout.taxes_not_enabled');
        }
        return $taxesEnabled;
    }
}
