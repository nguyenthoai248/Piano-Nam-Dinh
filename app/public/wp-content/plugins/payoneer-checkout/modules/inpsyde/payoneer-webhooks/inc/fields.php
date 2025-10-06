<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
use Syde\Vendor\Psr\Http\Message\UriInterface;
return new Factory(['webhooks.notification_url'], static function (UriInterface $notificationUrl): array {
    return ['webhook_endpoints_fieldset_title' => [
        /* translators: Title of the endpoint settings section */
        'title' => \__('Endpoints', 'payoneer-checkout'),
        'type' => 'title',
        'class' => 'section-payoneer-general',
    ], 'webhook_endpoints_endpoint_url' => [
        /* translators: Title of the endpoint URL settings field */
        'title' => \__('Payment notifications URL', 'payoneer-checkout'),
        'type' => 'text',
        'default' => (string) $notificationUrl,
        'description' => \__('Please make sure the URL is not blocked by a firewall', 'payoneer-checkout'),
        'class' => 'section-payoneer-general',
        'custom_attributes' => ['readonly' => 'readonly'],
    ]];
});
