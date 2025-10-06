<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Services\Service;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\PaymentWebhookController;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\PayoneerWebhooksController;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\OrderPaymentWebhookStrategyHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\LogIncomingWebhookRequest;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\CustomerRegistrationHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\FailedPaymentHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\ChargeBackPaymentHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\ChargedPaymentHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler\RefundedPaymentHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\WpRestApiControllerInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\OrderWebhookFinder\OrderWebhookFinder;
return static function (): array {
    $moduleRoot = \dirname(__DIR__);
    return [
        'webhooks.module_root_path' => new Value($moduleRoot),
        'webhooks.namespace' => new Alias('core.webhooks.namespace'),
        // Real permission check happens later, when the request is processed
        'webhooks.permission_callback' => new Value('__return_true'),
        'webhooks.callback' => new Factory(['webhooks.controller.webhooks_controller'], static function (WpRestApiControllerInterface $restApiController): callable {
            return static function (\WP_REST_Request $request) use ($restApiController): \WP_REST_Response {
                return $restApiController->handleWpRestRequest($request);
            };
        }),
        'webhooks.controller.payment_webhook_controller' => new Constructor(PaymentWebhookController::class, ['webhooks.order.security_header_field_name', 'wp.order_finder', 'webhooks.order_webhook_finder', 'webhooks.order.processed_id_field_name', 'webhooks.controller.payment_webhook_strategy_handler']),
        'webhooks.controller.payment_webhook_strategy_handler' => new Constructor(OrderPaymentWebhookStrategyHandler::class, ['webhooks.failed_payment_handler', 'webhooks.chargeback_payment_handler', 'webhooks.refunded_payment_handler', 'webhooks.charged_payment_handler', 'webhooks.customer_registration_handler']),
        'webhooks.log_incoming_webhooks_request' => new Constructor(LogIncomingWebhookRequest::class, ['webhooks.security_header_name']),
        'webhooks.failed_payment_handler' => new Constructor(FailedPaymentHandler::class),
        'webhooks.chargeback_payment_handler' => new Constructor(ChargeBackPaymentHandler::class),
        'webhooks.refunded_payment_handler' => new Constructor(RefundedPaymentHandler::class, ['wp.refund.service.orchestrator']),
        'webhooks.charged_payment_handler' => new Constructor(ChargedPaymentHandler::class, ['webhooks.order.charge_id_field_name']),
        'webhooks.customer_registration_handler' => new Constructor(CustomerRegistrationHandler::class, ['webhooks.customer_registration_id_field_name']),
        'webhooks.order_webhook_finder' => new Constructor(OrderWebhookFinder::class, ['webhooks.order.processed_id_field_name']),
        'webhooks.controller.webhooks_controller' => new Factory(['webhooks.controller.payment_webhook_controller'], static function (WpRestApiControllerInterface $paymentWebhookController): WpRestApiControllerInterface {
            return new PayoneerWebhooksController($paymentWebhookController);
        }),
        'webhooks.rest_route' => new Alias('core.webhooks.route'),
        'webhooks.allowed_methods' => static function (): array {
            //GET, POST. Payoneer doc says it's GET by default, but can be switched to POST by merchant.
            //https://www.optile.io/opg#8493049
            return [\WP_REST_Server::READABLE, \WP_REST_Server::CREATABLE];
        },
        'webhooks.settings.fields' => Service::fromFile("{$moduleRoot}/inc/fields.php"),
        'webhooks.security_header_name' => new Value('List-Security-Token'),
    ];
};
