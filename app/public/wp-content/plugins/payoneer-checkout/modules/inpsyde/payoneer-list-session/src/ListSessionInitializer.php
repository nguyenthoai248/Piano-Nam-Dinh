<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * This class just exists to reduce merge conflicts.
 * It keeps code out of ListSessionModule
 * and might very well be put there when it's time to merge
 */
class ListSessionInitializer
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * Fragment update: Init/Expiry handling for regular checkout
         */
        add_action('woocommerce_checkout_update_order_review', function () use ($container) {
            /**
             * The hook above runs too early for us, since the WC_Customer is updated immediately
             * afterward, while we need to use the updated data. So we hook into the customer save
             * process once to hook our logic
             */
            $this->hookOnce('woocommerce_after_calculate_totals', static function () use ($container): void {
                $gatewayEnabled = (bool) $container->get('checkout.payment_gateway.is_enabled');
                if (!$gatewayEnabled) {
                    return;
                }
                $context = new CheckoutContext();
                do_action('payoneer-checkout.init_list_session', $context);
            });
        }, 11);
        add_action('wp', static function () use ($container) {
            if (!is_checkout()) {
                return;
            }
            $gatewayEnabled = (bool) $container->get('checkout.payment_gateway.is_enabled');
            if (!$gatewayEnabled) {
                return;
            }
            /**
             * On the order-pay page, notices are rendered only once - before the payment gateways
             * So we don't get to add errors during LIST creation.
             * We create the LIST session early as a workaround. This pre-warms the cache for later
             * and allows us to create error messages in time
             */
            $isOrderPay = (bool) $container->get('wc.is_checkout_pay_page');
            /**
             * The WC core form submission (->POST request) goes through a very similar code path.
             * We cannot afford to generate WC error notices though, since _any_ notice would cause
             * the form submission to fail - even if another payment gateway is used!
             * So we only want to initialize the LIST in GET calls.
             */
            $isPost = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
            if ($isOrderPay && !$isPost) {
                /**
                 * @var \WC_Order $order
                 */
                $order = wc_get_order((int) $container->get('wc.order_under_payment'));
                $context = new PaymentContext($order);
                do_action('payoneer-checkout.init_list_session', $context);
            }
        });
        $this->registerListSessionInitializing($container);
    }
    protected function registerListSessionInitializing(ContainerInterface $container): void
    {
        add_action('payoneer-checkout.init_list_session', static function (ContextInterface $context) use ($container) {
            try {
                $manager = $container->get('list_session.manager');
                assert($manager instanceof ListSessionManager);
                $list = $manager->provide($context);
                $manager->persist($list, $context);
            } catch (\Throwable $exception) {
                //do nothing here.
                //todo: consider moving here our failed create list logic
            }
        });
    }
    /**
     * Register a WordPress hook in a way that callback is executed only once.
     *
     * @param string $hookName
     * @param callable $callable
     * @param int $priority
     * @param int $acceptedArgs
     *
     * @return void
     */
    protected function hookOnce(string $hookName, callable $callable, int $priority = 10, int $acceptedArgs = 1): void
    {
        /**
         * @psalm-suppress UnusedVariable
         */
        $once = static function () use (&$once, $hookName, $callable, $priority) {
            static $called = \false;
            /** @var callable $once */
            !$called and $callable(...func_get_args());
            $called = \true;
        };
        add_action($hookName, $once, $priority, $acceptedArgs);
    }
}
