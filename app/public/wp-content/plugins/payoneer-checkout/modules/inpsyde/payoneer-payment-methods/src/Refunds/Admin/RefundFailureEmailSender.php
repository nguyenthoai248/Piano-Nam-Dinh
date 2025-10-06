<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\Admin;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentMethods\Refunds\RefundTextContents;
/**
 * @todo add test cases for this class.
 */
class RefundFailureEmailSender
{
    private RefundTextContents $texts;
    public function __construct(RefundTextContents $texts)
    {
        $this->texts = $texts;
    }
    public function sendFailureForOrder(int $orderId): bool
    {
        $errorDetails = null;
        $message = $this->texts->failureEmailMessage($orderId);
        $subject = $this->texts->failureEmailSubject($orderId);
        $adminEmail = get_bloginfo('admin_email');
        $headers = $this->prepareHeaders(get_bloginfo('name'), $adminEmail);
        /**
         * Closure that captures the wp_mail error details, on failure.
         *
         * @param mixed $error Usually a WP_Error object.
         */
        $emailErrorCallback = static function ($error) use (&$errorDetails) {
            $errorDetails = $error;
        };
        add_action('wp_mail_failed', $emailErrorCallback);
        // Send the email using `wp_mail()` which is compatible with third-party SMTP plugins.
        $success = wp_mail($adminEmail, $subject, $message, $headers);
        remove_action('wp_mail_failed', $emailErrorCallback);
        if ($success) {
            do_action('payoneer-checkout.refund.failure-email-sent', ['recipient' => $adminEmail]);
            return \true;
        }
        do_action('payoneer-checkout.refund.failure-email-error', ['recipient' => $adminEmail, 'errorDetails' => $errorDetails]);
        return \false;
    }
    /**
     * Prepare email headers.
     *
     * @param string $senderName The name to use in the "From" header.
     * @param string $senderEmail The sender's email address to use in the "From" header.
     *
     * @return string[] List of email headers.
     */
    private function prepareHeaders(string $senderName, string $senderEmail): array
    {
        // Using esc_attr() as it escapes quotes and <> chars.
        $senderName = trim(esc_attr($senderName));
        $senderEmail = trim(esc_attr($senderEmail));
        return ['Content-Type: text/html; charset=UTF-8', 'From: ' . $senderName . ' <' . $senderEmail . '>'];
    }
}
