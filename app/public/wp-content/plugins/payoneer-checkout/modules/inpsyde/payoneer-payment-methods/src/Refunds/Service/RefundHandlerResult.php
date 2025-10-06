<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Service;

/**
 * Communicates the outcome of refund processing operations.
 */
final class RefundHandlerResult
{
    private const IGNORED = 0;
    private const IS_ELIGIBLE = 1;
    private const AWAITING_REFUND_OBJECT = 9;
    private const INSTANT_SUCCESS = 11;
    private const INSTANT_FAILURE = 12;
    private const ASYNC_PENDING = 20;
    private const ASYNC_SUCCESS = 21;
    private const ASYNC_FAILURE = 22;
    /**
     * Any of these statuses mean, that the RefundOrchestrator understood and
     * acted on the request
     */
    private const HANDLED_STATUSES = [self::IS_ELIGIBLE, self::INSTANT_SUCCESS, self::INSTANT_FAILURE, self::ASYNC_PENDING, self::ASYNC_SUCCESS, self::ASYNC_FAILURE, self::AWAITING_REFUND_OBJECT];
    private const SUCCESS_STATUSES = [self::INSTANT_SUCCESS, self::ASYNC_SUCCESS];
    private const FAILURE_STATUSES = [self::INSTANT_FAILURE, self::ASYNC_FAILURE];
    private const PENDING_STATUSES = [self::ASYNC_PENDING, self::AWAITING_REFUND_OBJECT];
    private int $status;
    private string $statusMessage;
    public function __construct(int $status, string $statusMessage)
    {
        $this->status = $status;
        $this->statusMessage = $statusMessage;
    }
    /**
     * @return bool True when the refund request was handled by the orchestrator.
     */
    public function handled(): bool
    {
        return in_array($this->status, self::HANDLED_STATUSES, \true);
    }
    /**
     * @return bool True for async refunds awaiting webhook confirmation
     */
    public function waitingForWebhook(): bool
    {
        return in_array($this->status, self::PENDING_STATUSES, \true);
    }
    /**
     * @return bool True when processing completed successfully
     */
    public function successful(): bool
    {
        return in_array($this->status, self::SUCCESS_STATUSES, \true);
    }
    /**
     * @return bool True when a refund explicitly failed
     */
    public function failed(): bool
    {
        return in_array($this->status, self::FAILURE_STATUSES, \true);
    }
    /**
     * @return bool True when orchestrator needs the WC_Order_Refund object
     */
    public function missingRefundData(): bool
    {
        return $this->status === self::AWAITING_REFUND_OBJECT;
    }
    /**
     * @return string Human-readable status for logging or UI display
     */
    public function statusMessage(): string
    {
        return $this->statusMessage;
    }
    // Result Factories -----
    public static function notHandled(string $statusMessage = 'Refund not eligible for processing'): self
    {
        return new self(self::IGNORED, $statusMessage);
    }
    public static function isEligible(string $statusMessage = 'Refund eligible for processing'): self
    {
        return new self(self::IS_ELIGIBLE, $statusMessage);
    }
    public static function processedImmediately(string $statusMessage = 'Refund accepted instantly'): self
    {
        return new self(self::INSTANT_SUCCESS, $statusMessage);
    }
    public static function failedImmediately(string $statusMessage = 'Refund failed instantly'): self
    {
        return new self(self::INSTANT_FAILURE, $statusMessage);
    }
    public static function awaitingWebhook(string $statusMessage = 'Async refund initiated, awaiting confirmation'): self
    {
        return new self(self::ASYNC_PENDING, $statusMessage);
    }
    public static function webhookSuccess(string $statusMessage = 'Async refund completed successfully'): self
    {
        return new self(self::ASYNC_SUCCESS, $statusMessage);
    }
    public static function webhookFailure(string $statusMessage = 'Async refund failed'): self
    {
        return new self(self::ASYNC_FAILURE, $statusMessage);
    }
    public static function awaitingRefundObject(string $statusMessage = 'Need a WC_Order_Refund object to proceed'): self
    {
        return new self(self::AWAITING_REFUND_OBJECT, $statusMessage);
    }
}
