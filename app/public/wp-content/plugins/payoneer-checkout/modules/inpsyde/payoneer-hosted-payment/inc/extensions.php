<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Collection\MapInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentFieldsRenderer\DescriptionFieldRenderer;
use Syde\Vendor\Psr\Container\ContainerInterface;
return static function (): array {
    return ['checkout.flow_options' => static function (array $paymentFlowOptions): array {
        $paymentFlowOptions['hosted'] = \__('Hosted', 'payoneer-checkout');
        return $paymentFlowOptions;
    }, 'checkout.flow_options_description' => static function (string $paymentOptionsDescription): string {
        /* translators: Payment flow dropdown entry in the gateway settings */
        $hostedDescription = \__('Hosted: customers are redirected to a payment page hosted by Payoneer.', 'payoneer-checkout');
        $paymentOptionsDescription .= '<br>' . $hostedDescription;
        return $paymentOptionsDescription;
    }, 'checkout.payment_field_renderers' => static function (array $renderers, ContainerInterface $container): array {
        $isEnabled = (bool) $container->get('hosted_payment.is_enabled');
        if (!$isEnabled) {
            return $renderers;
        }
        /** @var MapInterface */
        $options = $container->get('inpsyde_payment_gateway.options');
        if (!$options->has('description')) {
            return $renderers;
        }
        $description = (string) $options->get('description');
        if (empty($description)) {
            return $renderers;
        }
        $renderers[] = new DescriptionFieldRenderer($description);
        return $renderers;
    }, 'inpsyde_payment_gateway.has_fields' => static function (bool $hasFields, ContainerInterface $container): bool {
        $isEnabled = (bool) $container->get('hosted_payment.is_enabled');
        if ($isEnabled) {
            return \false;
        }
        return $hasFields;
    }];
};
