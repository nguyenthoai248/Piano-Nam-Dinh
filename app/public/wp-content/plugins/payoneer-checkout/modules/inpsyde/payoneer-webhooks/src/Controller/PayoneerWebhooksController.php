<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\Controller;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\WebhookEntities;
use WP_REST_Request;
use WP_REST_Response;
/**
 * The main webhooks controller. It handles request first and decides what to do next:
 * give it to another controller or return response immediately.
 */
class PayoneerWebhooksController implements WpRestApiControllerInterface
{
    /**
     * @var WpRestApiControllerInterface
     */
    protected $paymentWebhookController;
    /**
     * @param WpRestApiControllerInterface $paymentWebhookController
     */
    public function __construct(WpRestApiControllerInterface $paymentWebhookController)
    {
        $this->paymentWebhookController = $paymentWebhookController;
    }
    /**
     * @inheritDoc
     */
    public function handleWpRestRequest(WP_REST_Request $request): WP_REST_Response
    {
        do_action('payoneer-checkout.webhook_request', $request);
        $response = null;
        if ($this->getWebhookEntity($request) === WebhookEntities::PAYMENT) {
            $response = $this->paymentWebhookController->handleWpRestRequest($request);
        }
        $response = $response ?? new WP_REST_Response(null, 200);
        do_action('payoneer-checkout.webhook_response', $response);
        return $response;
    }
    /**
     * Get webhook entity (what this incoming webhook is about).
     *
     * @param WP_REST_Request $request Request to get data from.
     *
     * @return string Webhook entity type.
     */
    protected function getWebhookEntity(WP_REST_Request $request): string
    {
        return (string) $request->get_param('entity');
    }
}
