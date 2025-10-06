<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
/**
 * @see RefundContextTest - unit tests for this class.
 */
class RefundContext
{
    private const STATUS_SUCCESS = ['paid_out', 'paid_out_partial'];
    private const STATUS_PENDING = ['pending'];
    private const REASON_REFUND_CODES = ['refund_credited', 'refund_declined', 'refund_rejected', 'refund_failed'];
    private bool $wasParsed = \false;
    private string $longId = '';
    private string $statusCode = '';
    private string $reasonCode = '';
    private ?string $previousReasonCode = null;
    private float $amount = 0;
    private string $reason = '';
    // Private, to ensure a factory method is used to initialize the object.
    private function __construct()
    {
    }
    /**
     * Extracts relevant information from an API call's LIST response.
     *
     * This factory is only called after making a dedicated payout request; we can assume
     * that it's always describing a refund status.
     *
     * @throws ApiExceptionInterface
     */
    public static function fromList(ListInterface $list): self
    {
        $context = new self();
        // Extract raw data from LIST
        $rawData = [
            'longId' => $list->getIdentification()->getLongId(),
            'statusCode' => $list->getStatus()->getCode(),
            'reasonCode' => $list->getStatus()->getReason(),
            'previousReasonCode' => null,
            // LIST responses don't have a previous reason.
            'reason' => $list->getPayment()->getReference(),
            'amount' => $list->getPayment()->getAmount(),
        ];
        // Allow modification for testing/debugging
        $rawData = (array) apply_filters('payoneer-checkout.refund-context-raw-data', $rawData, ['source' => 'list', 'data' => $list]);
        return self::assignFromRawData($context, $rawData);
    }
    /**
     * Inspects an incoming REST request (i.e. a notification webhook) and extracts
     * refund details from the payload.
     *
     * Webhook data is untrusted, and we add a basic sanity check before parsing it.
     *
     * This factory is used by the notification handler to inspect a webhook, and might
     * receive data from unrelated requests. We need to ensure this factory only acts
     * on payout-relevant details.
     */
    public static function fromRestRequest(\WP_REST_Request $request): self
    {
        $context = new self();
        $longId = $request->get_param('longId');
        $amount = $request->get_param('amount');
        $isPayment = 'payment' === $request->get_param('entity');
        if (!$isPayment || !$longId || (float) $amount <= 0) {
            return $context;
        }
        // Extract raw data from request
        $rawData = ['longId' => (string) $longId, 'statusCode' => (string) $request->get_param('statusCode'), 'reasonCode' => (string) $request->get_param('reasonCode'), 'previousReasonCode' => (string) $request->get_param('previousReasonCode'), 'reason' => (string) $request->get_param('reference'), 'amount' => (float) $amount];
        // Allow modification for testing/debugging
        $rawData = (array) apply_filters('payoneer-checkout.refund-context-raw-data', $rawData, ['source' => 'webhook', 'data' => $request]);
        return self::assignFromRawData($context, $rawData);
    }
    /**
     * Common helper method to assign raw data to context properties.
     *
     * This centralizes the property assignment logic and ensures consistency
     * between both factory methods.
     */
    private static function assignFromRawData(self $context, array $rawData): self
    {
        $context->wasParsed = \true;
        $context->longId = $rawData['longId'] ?? '';
        $context->statusCode = $rawData['statusCode'] ?? '';
        $context->reasonCode = $rawData['reasonCode'] ?? '';
        $context->previousReasonCode = $rawData['previousReasonCode'] ?? null;
        $context->reason = $rawData['reason'] ?? '';
        $context->amount = $rawData['amount'] ?? 0;
        return $context;
    }
    public function wasParsed(): bool
    {
        return $this->wasParsed;
    }
    public function isPending(): bool
    {
        return in_array($this->statusCode, self::STATUS_PENDING, \true);
    }
    public function isFinished(): bool
    {
        return !$this->isPending();
    }
    public function wasSuccessful(): bool
    {
        return in_array($this->statusCode, self::STATUS_SUCCESS, \true);
    }
    public function didFail(): bool
    {
        return $this->isFinished() && !$this->wasSuccessful();
    }
    /**
     * Whether the webhook notification contains a refund-related reason, either in
     * the current or the previous state.
     */
    public function hasRefundReason(bool $previousReason = \false): bool
    {
        if ($previousReason) {
            return in_array((string) $this->previousReasonCode, self::REASON_REFUND_CODES, \true);
        }
        return in_array($this->reasonCode, self::REASON_REFUND_CODES, \true);
    }
    public function statusCode(): string
    {
        return $this->statusCode;
    }
    public function longId(): string
    {
        return $this->longId;
    }
    public function reasonCode(): string
    {
        return $this->reasonCode;
    }
    public function reason(): string
    {
        return $this->reason;
    }
    public function amount(): float
    {
        return $this->amount;
    }
}
