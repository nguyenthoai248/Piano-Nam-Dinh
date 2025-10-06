<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway;

use Syde\Vendor\Psr\Container\ContainerInterface;
return static function () : array {
    return ['embedded_payment.assets.can_enqueue' => static function (callable $previous, ContainerInterface $container) : callable {
        $canUsePaymentGateway = $container->get('inpsyde_payment_gateway.gateway.can_be_used');
        assert(is_callable($canUsePaymentGateway));
        return static function () use($previous, $canUsePaymentGateway) : bool {
            return $previous() && $canUsePaymentGateway();
        };
    }, 'core.path_resolver.mappings' => static function (array $prev, ContainerInterface $container) : array {
        $moduleName = $container->get('inpsyde_payment_gateway.module_name');
        $moduleDir = $container->get('inpsyde_payment_gateway.module_dir');
        return ["{$moduleName}/" => "{$moduleDir}/"] + $prev;
    }, 'core.url_resolver.mappings' => static function (array $prev, ContainerInterface $container) : array {
        $moduleDir = $container->get('inpsyde_payment_gateway.module_dir');
        $moduleUrl = $container->get('inpsyde_payment_gateway.module_url');
        $map = ["{$moduleDir}/" => "{$moduleUrl}/"];
        return $map + $prev;
    }];
};
