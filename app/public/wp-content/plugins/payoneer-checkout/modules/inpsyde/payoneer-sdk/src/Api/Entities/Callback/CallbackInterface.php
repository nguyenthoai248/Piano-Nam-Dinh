<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;
/**
 * Represents a collection information about merchants shop system.
 */
interface CallbackInterface
{
    /**
     * Return URL in merchant shop where customer will be redirected after successful payment.
     *
     * @return string The URL.
     */
    public function getReturnUrl(): string;
    /**
     * Return URL in merchant shop where customer will be redirected after cancelled or
     * permanently failed payment.
     *
     * @return string The URL.
     */
    public function getCancelUrl(): string;
    /**
     * Return URL of landing page in merchants shop system after customer select payment method.
     *
     * @return string The URL.
     */
    public function getSummaryUrl(): string;
    /**
     * Return URL for webhooks calls.
     *
     * @return string The URL.
     *
     * @throws ApiExceptionInterface If this field not set.
     */
    public function getNotificationUrl(): string;
    /**
     * Return notification headers defined by merchant.
     *
     * An array of additional merchant specific headers.
     * These headers will be set and send back with OPG notifications.
     *
     * @return HeaderInterface[] The list of attached notification headers.
     */
    public function getNotificationHeaders(): array;
}
