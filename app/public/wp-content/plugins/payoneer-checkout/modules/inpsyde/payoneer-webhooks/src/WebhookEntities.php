<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks;

/**
 * Enumeration of possible webhooks entities - subjects webhooks can be received about.
 *
 * @see https://checkoutdocs.payoneer.com/docs/create-notification-endpoints#status-entities
 */
class WebhookEntities
{
    public const PAYMENT = 'payment';
    public const CUSTOMER = 'customer';
    public const ACCOUNT = 'account';
    public const SESSION = 'session';
}
