<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportParamsDTO;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Data\SystemReportDataDTO;
/**
 * Implementation of the SystemReportEmailSenderInterface.
 *
 * Sends system report data via email with appropriate attachments.
 *
 * @psalm-type Attachment array{filename: string, content: string, mime_type: string}
 * @phpstan-type Attachment array{filename: string, content: string, mime_type: string}
 */
class SystemReportEmailSender implements SystemReportEmailSenderInterface
{
    /**
     * Default MIME type for attachments.
     */
    private const DEFAULT_MIME_TYPE = 'text/plain';
    /**
     * Recipient of the system report email. Can only be set via the constructor.
     *
     * @var string
     */
    private string $recipient;
    /**
     * The email subject template.
     *
     * @var string
     */
    private string $subjectTemplate;
    /**
     * Template of the email body (HTML allowed).
     *
     * @var string
     */
    private string $messageTemplate;
    /**
     * The formatter used to convert raw data to email-friendly format.
     *
     * @var SystemReportFormatterInterface
     */
    private SystemReportFormatterInterface $formatter;
    /**
     * Contains merchant details, which are used to enrich the email message with additional
     * information.
     *
     * @var MerchantInterface
     */
    private MerchantInterface $merchant;
    /**
     * SystemReportEmailSender constructor.
     *
     * @param string $recipient The email recipient address.
     * @param string $subjectTemplate The email subject.
     * @param string $messageTemplate The email message.
     * @param SystemReportFormatterInterface $formatter The report formatter.
     * @param MerchantInterface $merchant Merchant details.
     */
    public function __construct(string $recipient, string $subjectTemplate, string $messageTemplate, SystemReportFormatterInterface $formatter, MerchantInterface $merchant)
    {
        $this->recipient = $recipient;
        $this->subjectTemplate = $subjectTemplate;
        $this->messageTemplate = $messageTemplate;
        $this->formatter = $formatter;
        $this->merchant = $merchant;
    }
    /**
     * @inheritDoc
     */
    public function sendReport(SystemReportDataDTO $reportData, SystemReportParamsDTO $params): bool
    {
        $attachments = $this->prepareAttachments($reportData);
        $placeholders = $this->createPlaceholders($params, $reportData);
        $errorDetails = null;
        $message = $this->prepareMessage($placeholders);
        $subject = $this->prepareSubject($placeholders);
        $headers = $this->prepareHeaders($placeholders['SITE_NAME'], get_bloginfo('admin_email'));
        // Closure that adds email attachments from in-memory strings.
        $attachmentCallback = $this->createAttachmentCallback($attachments);
        /**
         * Closure that captures the wp_mail error details, on failure.
         *
         * @param mixed $error Usually a WP_Error object.
         */
        $emailErrorCallback = static function ($error) use (&$errorDetails) {
            $errorDetails = $error;
        };
        add_action('phpmailer_init', $attachmentCallback);
        add_action('wp_mail_failed', $emailErrorCallback);
        // Send the email using `wp_mail()` which is compatible with third-party SMTP plugins.
        $success = wp_mail($this->recipient, $subject, $message, $headers);
        remove_action('phpmailer_init', $attachmentCallback);
        remove_action('wp_mail_failed', $emailErrorCallback);
        if ($success) {
            do_action('payoneer-checkout.status-report.email-sent');
            return \true;
        }
        do_action('payoneer-checkout.status-report.email-failed', ['errorDetails' => $errorDetails]);
        return \false;
    }
    /**
     * Prepare all attachments from the report data.
     *
     * @param SystemReportDataDTO $reportData The report data
     *
     * @return list<Attachment> List of attachments.
     */
    private function prepareAttachments(SystemReportDataDTO $reportData): array
    {
        /** @var list<Attachment> */
        return [...$this->createSystemReportAttachment($reportData), ...$this->createOrderDetailAttachments($reportData), ...$this->createLogAttachment($reportData)];
    }
    /**
     * Creates the system report HTML attachment.
     *
     * @param SystemReportDataDTO $reportData The report data.
     *
     * @return array<int, Attachment> The attachment data or empty array if no content.
     */
    private function createSystemReportAttachment(SystemReportDataDTO $reportData): array
    {
        $statusReport = $reportData->getStatusReport();
        if (empty($statusReport)) {
            return [];
        }
        $formattedReport = $this->formatter->formatSystemStatus($statusReport);
        /** @var array<int, Attachment> */
        return [['content' => $formattedReport, 'filename' => 'system-status.html', 'mime_type' => 'text/html']];
    }
    /**
     * Creates the order detail attachments.
     *
     * @param SystemReportDataDTO $reportData The report data.
     *
     * @return array<int, Attachment> List of order report attachments.
     */
    private function createOrderDetailAttachments(SystemReportDataDTO $reportData): array
    {
        $attachments = [];
        $orders = $reportData->getOrders();
        foreach ($orders as $orderId => $orderData) {
            $formattedOrder = $orderData instanceof \WC_Abstract_Order ? $this->formatter->formatOrder((string) $orderId, $orderData) : $this->formatter->formatOrderError((string) $orderId, $orderData);
            $attachments[] = ['content' => $formattedOrder, 'filename' => sprintf('order-%s.html', $orderId), 'mime_type' => 'text/html'];
        }
        /** @var array<int, Attachment> */
        return $attachments;
    }
    /**
     * Creates the log file attachment.
     *
     * @param SystemReportDataDTO $reportData The report data.
     *
     * @return array<int, Attachment> The log attachment or empty array if no content.
     */
    private function createLogAttachment(SystemReportDataDTO $reportData): array
    {
        $logContent = $reportData->getLogContent();
        $logDate = $reportData->getLogDate();
        if (!$logDate || empty($logContent)) {
            return [];
        }
        $formattedLogContent = $this->formatter->formatLogContent($logContent);
        /** @var array<int, Attachment> */
        return [['content' => $formattedLogContent, 'filename' => sprintf('log-%s.log', $logDate), 'mime_type' => 'text/plain']];
    }
    /**
     * Create the basic placeholders for email templates.
     *
     * @return array<string, string> Key-value pairs of placeholders.
     */
    private function createPlaceholders(SystemReportParamsDTO $params, SystemReportDataDTO $reportData): array
    {
        $orderIds = $params->getOrderIds();
        $logDate = $params->getLogDate();
        $logInfo = '-';
        if ($logDate) {
            $logInfo = sprintf('%s, from %s', $logDate, $reportData->getLogSource());
        }
        return ['SITE_NAME' => get_bloginfo('name'), 'SITE_URL' => get_bloginfo('url'), 'ORDER_IDS' => $orderIds ? implode('<br/>', $orderIds) : '-', 'LOG_INFO' => $logInfo, 'MERCHANT_CODE' => $this->merchant->getCode(), 'STORE_CODE' => $this->merchant->getDivision()];
    }
    /**
     * Returns the fully parsed email body.
     *
     * @param array<string, string> $placeholders Key-value pairs of placeholders.
     *
     * @return string The fully parsed email body.
     */
    private function prepareMessage(array $placeholders): string
    {
        return $this->replacePlaceholders($this->messageTemplate, $placeholders);
    }
    /**
     * Returns the final email subject.
     *
     * @param array<string, string> $placeholders Key-value pairs of placeholders.
     *
     * @return string The email subject.
     */
    private function prepareSubject(array $placeholders): string
    {
        return $this->replacePlaceholders($this->subjectTemplate, $placeholders);
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
    /**
     * Create a callback function for handling attachments.
     *
     * @param Attachment[] $attachments List of attachments.
     *
     * @return callable The callback function for phpmailer_init.
     */
    // phpcs:ignore Inpsyde.CodeQuality.NestingLevel.High -- intentionally ignoring this
    private function createAttachmentCallback(array $attachments): callable
    {
        /**
         * Runs custom attachment logic when the PHPMailer instance is initialized.
         *
         * @param PHPMailer|object $phpmailer The PHPMailer instance.
         */
        return static function (object $phpmailer) use ($attachments) {
            if (!is_callable([$phpmailer, 'addStringAttachment'])) {
                do_action('payoneer-checkout.status-report.cannot-add-attachments');
                return;
            }
            /**
             * Add type-hinting for $phpmailer, it should be a PHPMailer instance.
             *
             * @var PHPMailer $phpmailer
             */
            foreach ($attachments as $attachment) {
                try {
                    $result = $phpmailer->addStringAttachment($attachment['content'], $attachment['filename'], 'base64', $attachment['mime_type'] ?? self::DEFAULT_MIME_TYPE);
                } catch (Exception $error) {
                    $result = \false;
                }
                if (!$result) {
                    do_action('payoneer-checkout.status-report.attachment-failed', ['filename' => $attachment['filename']]);
                }
            }
        };
    }
    /**
     * Replaces {PLACEHOLDER} style placeholders in the message template.
     *
     * @param string $template The message template, containing placeholders.
     * @param array<string, string> $replacements Key-value pairs to replace placeholders.
     *
     * @return string The modified message with placeholders replaced.
     */
    private function replacePlaceholders(string $template, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $placeholder = strtoupper($placeholder);
            $template = str_replace('{' . $placeholder . '}', wp_kses($value, ['br' => []]), $template);
        }
        return $template;
    }
}
