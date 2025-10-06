<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGenerator;
use Syde\Vendor\Inpsyde\Wp\HttpClient\Client as WpClient;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Psr\Http\Client\ClientInterface;
use WC_Session;
use WC_Session_Handler;
return static function (): array {
    return ['core.path_resolver.mappings' => static function (array $previous, ContainerInterface $container): array {
        /** @var string $sourcePath */
        $sourcePath = $container->get('checkout.templates_dir_virtual_path');
        /** @var string $destinationPath */
        $destinationPath = $container->get('checkout.templates_dir_local_path');
        return array_merge($previous, [$sourcePath => $destinationPath]);
    }, 'wc.session' => static function (WC_Session $session, ContainerInterface $container): WC_Session {
        $tokenField = (string) $container->get('checkout.order.security_header_field_name');
        assert($session instanceof WC_Session_Handler || count(array_intersect(['has_session', 'init_session_cookie', 'set_customer_session_cookie'], get_class_methods($session))));
        if (empty($session->get($tokenField))) {
            $tokenGenerator = $container->get('checkout.security_token_generator');
            assert($tokenGenerator instanceof TokenGenerator);
            $session->set($tokenField, $tokenGenerator->generateToken());
        }
        return $session;
    }, 'payoneer_settings.settings_fields' => static function (array $previous, ContainerInterface $container): array {
        /** @var array<string, array-key> $generalSettingsFields */
        $generalSettingsFields = $container->get('checkout.settings.general_settings_fields');
        return array_merge($previous, $generalSettingsFields);
    }, 'payoneer_sdk.http_client' => static function (ClientInterface $previous, ContainerInterface $container): ClientInterface {
        /**
         * Our decorator works by modifying WP hooks, so we only apply it if our
         * own WP-based PSR-7 client is used for API calls
         */
        if (!$previous instanceof WpClient) {
            return $previous;
        }
        $timeout = (int) $container->get('checkout.http_request_timeout');
        return new TimeoutIncreasingApiClient($previous, $timeout);
    }];
};
