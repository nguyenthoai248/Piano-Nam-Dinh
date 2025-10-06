<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\Status;

/**
 * Represents current status of the payment session.
 */
interface StatusInterface
{
    /**
     * Return status code.
     *
     * Valid values are: "pending", "failed", "declined", "aborted", "expired", "canceled",
     * "listed", "preauthorized", "charged", "paid_out", "approval_requested", "charged_back",
     * "information_requested", "dispute_closed", "depleated", "registered", "deregistered",
     * "subscribed", "unsubscribed", "used", "rejected", "activated", "functioning", "ended"
     *
     * @return string
     *
     * @see https://www.optile.io/opg#285186
     */
    public function getCode(): string;
    /**
     * Return reason phrase explaining status, complements status code.
     *
     * @return string
     */
    public function getReason(): string;
}
