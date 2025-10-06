<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\AdminNotice;

class AdminNoticeHooks
{
    private const ACTION_DISMISS = 'payoneer-checkout.admin-notice.dismiss';
    private static function dismissAction(string $dismissType): string
    {
        return self::ACTION_DISMISS . ".{$dismissType}";
    }
    private static function logAction(): string
    {
        return self::ACTION_DISMISS;
    }
    public static function dismiss(string $dismissType, int $dismissId): void
    {
        /**
         * This hook notifies the relevant module to dismiss a specific notice.
         *
         * Sample
         * - Type: "async_refund":
         * - Hook: "payoneer-checkout.admin-notice.dismiss.async_refund"
         *
         * @param int $dismissId ID of the dismissed item.
         */
        do_action(self::dismissAction($dismissType), $dismissId);
    }
    public static function log(string $dismissType, int $dismissId): void
    {
        /**
         * Generic action used for logging.
         *
         * The action name is static, and all details are collected in a key-value pair array.
         */
        do_action(self::logAction(), ['type' => $dismissType, 'id' => $dismissId]);
    }
    public static function onDismiss(string $dismissType, callable $handler): void
    {
        add_action(self::dismissAction($dismissType), $handler);
    }
}
