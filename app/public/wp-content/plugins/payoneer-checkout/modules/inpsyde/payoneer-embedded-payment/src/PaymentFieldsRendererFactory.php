<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Syde\Vendor\Inpsyde\PaymentGateway\PaymentFieldsRendererInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer\ListDebugFieldRenderer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer\WidgetPlaceholderFieldRenderer;
use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
class PaymentFieldsRendererFactory
{
    /**
     * @param string $component
     * @param ContainerInterface $container
     * @param string $description
     *
     * @return list<PaymentFieldsRendererInterface>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function forComponent(string $component, ContainerInterface $container, string $description): array
    {
        $renderers = [];
        $isCheckout = (bool) $container->get('wc.is_checkout');
        $isFragmentUpdate = (bool) $container->get('wc.is_fragment_update');
        $isOrderPay = (bool) $container->get('wc.is_checkout_pay_page');
        $shouldRenderList = $isFragmentUpdate || $isOrderPay;
        if (!($isCheckout || $shouldRenderList)) {
            return $renderers;
        }
        $hostedFlowOverrideFlag = $container->get('embedded_payment.payment_fields_renderer.hosted_override_flag');
        assert($hostedFlowOverrideFlag instanceof PaymentFieldsRendererInterface);
        $renderers[] = $hostedFlowOverrideFlag;
        $renderers[] = new WidgetPlaceholderFieldRenderer('payoneer-payment-fields-container', 'data-component', $component, $description);
        $isDebug = (bool) $container->get('checkout.is_debug');
        if ($isDebug && $shouldRenderList) {
            $debugRenderer = $container->get('embedded_payment.payment_fields_renderer.debug');
            assert($debugRenderer instanceof ListDebugFieldRenderer);
            $renderers[] = $debugRenderer;
        }
        return $renderers;
    }
}
