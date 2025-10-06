<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds;

class RefundStatusDefinition
{
    // Valid for all refunds.
    public const STATUS_NONE = '';
    public const STATUS_API_CALL = 'api-call';
    public const STATUS_SUCCESS = 'success';
    // Only for async refunds via webhook.
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    private const VALID_TRANSITIONS = [self::STATUS_NONE => [self::STATUS_API_CALL, self::STATUS_SUCCESS], self::STATUS_API_CALL => [self::STATUS_PENDING, self::STATUS_SUCCESS, self::STATUS_NONE], self::STATUS_PENDING => [self::STATUS_SUCCESS, self::STATUS_FAILED], self::STATUS_SUCCESS => [self::STATUS_PENDING, self::STATUS_FAILED, self::STATUS_API_CALL], self::STATUS_FAILED => [self::STATUS_NONE]];
    public static function isValidTransition(string $from, string $to): bool
    {
        return in_array($to, self::VALID_TRANSITIONS[$from] ?? [], \true);
    }
}
