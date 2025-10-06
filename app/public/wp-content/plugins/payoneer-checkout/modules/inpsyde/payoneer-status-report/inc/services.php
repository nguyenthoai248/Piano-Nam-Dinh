<?php

/**
 * The status report module services.
 *
 * @package Inpsyde\PayoneerForWoocommerce\StatusReport
 */
declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport;

use WP_REST_Request;
use WP_REST_Response;
use Syde\Vendor\Psr\Container\ContainerInterface;
use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Dhii\Services\Service;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportCollector;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportParamsDTO;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\LogCollector\LogCollectorFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email\SystemReportEmailSender;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email\SystemReportFormatter;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Endpoint\SystemReportEndpointController;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Permission\SystemReportPermissionHandler;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller\WpRestApiControllerInterface;
return static function (): array {
    $moduleRoot = dirname(__DIR__);
    return ['status-report.renderer' => static function (): Renderer {
        return new Renderer();
    }, 'status-report.fields' => static function (ContainerInterface $container): array {
        return [['label' => esc_html__('Shop country code', 'payoneer-checkout'), 'exported_label' => 'Shop country code', 'description' => esc_html__('Country / State value on Settings / General / Store Address.', 'payoneer-checkout'), 'value' => $container->get('inpsyde_payment_gateway.store_country')], ['label' => esc_html__('API username', 'payoneer-checkout'), 'exported_label' => 'API username', 'value' => $container->get('payoneer_settings.merchant_code')], ['label' => esc_html__('Payment flow', 'payoneer-checkout'), 'exported_label' => 'Payment flow', 'description' => esc_html__('Displays whether a plugin is using a hosted or an embedded payment flow', 'payoneer-checkout'), 'value' => $container->get('checkout.selected_payment_flow')]];
    }, 'status-report.options' => new Alias('core.options'), 'status-report.namespace' => new Alias('core.webhooks.namespace'), 'status-report.rest_route' => new Value('/system-report'), 'status-report.allowed_methods' => new Value(['POST']), 'status-report.html-template' => Service::fromFile("{$moduleRoot}/inc/html-template.php"), 'status-report.email.recipient' => new Value('l2checkoutsupport@payoneer.com'), 'status-report.email.subject' => new Value('Automated System Report - {SITE_NAME}'), 'status-report.email.message' => Service::fromFile("{$moduleRoot}/inc/email-message.php"), 'status-report.email.formatter' => new Constructor(SystemReportFormatter::class, ['core.plugin.version_string', 'status-report.html-template']), 'status-report.email.sender' => new Constructor(SystemReportEmailSender::class, ['status-report.email.recipient', 'status-report.email.subject', 'status-report.email.message', 'status-report.email.formatter', 'payoneer_settings.merchant']), 'status-report.background_processor' => new Factory([], static function (): callable {
        return static function (SystemReportParamsDTO $params): array {
            // Schedule the task as a background job: This is where the actual work happens.
            $scheduled = wp_schedule_single_event(time(), 'payoneer_process_system_report', [$params], \true);
            if (\true !== $scheduled) {
                return ['success' => \false, 'message' => $scheduled->get_error_message()];
            }
            return ['success' => \true];
        };
    }), 'status-report.controller' => new Constructor(SystemReportEndpointController::class, ['status-report.background_processor']), 'status-report.callback' => new Factory(['status-report.controller'], static function (WpRestApiControllerInterface $controller): callable {
        return static fn(WP_REST_Request $request): WP_REST_Response => $controller->handleWpRestRequest($request);
    }), 'status-report.permission_handler' => new Constructor(SystemReportPermissionHandler::class, ['status-report.options', 'payoneer_settings.merchant']), 'status-report.permission_callback' => new Factory(['status-report.permission_handler'], static function (SystemReportPermissionHandler $handler): callable {
        return static fn(WP_REST_Request $request) => $handler->checkPermission($request);
    }), 'status-report.log-file-collector-factory' => new Constructor(LogCollectorFactory::class, ['inpsyde_logger.logging_source']), 'status-report.collector' => new Constructor(SystemReportCollector::class, ['status-report.log-file-collector-factory', 'wp.order_finder']), 'status-report.settings.fields' => Service::fromFile("{$moduleRoot}/inc/fields.php")];
};
