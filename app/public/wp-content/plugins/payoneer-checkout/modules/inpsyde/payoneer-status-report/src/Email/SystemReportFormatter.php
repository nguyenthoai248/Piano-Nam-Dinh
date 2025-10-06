<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\Email;

use WC_Abstract_Order;
use WC_Order;
use WP_Error;
/**
 * Formats raw system report data for email delivery.
 *
 * This service transforms the raw collected data into properly formatted
 * HTML documents and text content suitable for email attachments.
 */
class SystemReportFormatter implements SystemReportFormatterInterface
{
    /**
     * Version of the plugin.
     *
     * @var string
     */
    private string $pluginVersion;
    /**
     * This template is used to build the HTML documents which are attached to the email.
     *
     * @var string
     */
    private string $htmlTemplate;
    public function __construct(string $pluginVersion, string $htmlTemplate)
    {
        $this->pluginVersion = $pluginVersion;
        $this->htmlTemplate = $htmlTemplate;
    }
    /**
     * @inheritDoc
     */
    public function formatSystemStatus(string $rawSystemStatus): string
    {
        $systemStatus = trim($rawSystemStatus);
        // Remove content before the first table-tag.
        $tablePosition = stripos($systemStatus, '<table');
        if ($tablePosition !== \false) {
            $systemStatus = (string) substr($rawSystemStatus, $tablePosition);
        }
        // Strip the <td class="help"> columns.
        $filteredStatus = preg_replace('/<td class="help">.*?<\/td>/si', '', $systemStatus);
        if ($filteredStatus !== null) {
            $systemStatus = $filteredStatus;
        }
        return $this->buildHtmlDocument(['title' => 'System Status Report', 'body' => $systemStatus]);
    }
    /**
     * @inheritDoc
     */
    public function formatOrder(string $orderId, WC_Abstract_Order $orderData): string
    {
        // Build order content.
        $body = [$this->getOrderDetailsHtml($orderData), $this->getOrderItemsHtml($orderData), $this->getPaymentDetailsHtml($orderData), $this->getOrderNotesHtml($orderData)];
        return $this->buildHtmlDocument(['title' => 'Order #' . esc_html($orderId), 'body' => implode('', $body)]);
    }
    /**
     * @inheritDoc
     */
    public function formatLogContent(string $logContent): string
    {
        // For logs, we simply return the raw content as it should already
        // be properly formatted for text display
        return $logContent;
    }
    /**
     * Format error information about an order.
     *
     * @param string $orderId The order ID.
     * @param WP_Error $error The error information.
     *
     * @return string HTML content.
     */
    public function formatOrderError(string $orderId, WP_Error $error): string
    {
        $errorCode = (string) $error->get_error_code();
        $errorMessage = $error->get_error_message();
        return $this->buildHtmlDocument(['title' => 'Error: Order #' . esc_html($orderId), 'body' => sprintf('<p class="error-code">Error: %s</p><p class="error-message">%s</p>', esc_html($errorCode), esc_html($errorMessage)), 'error' => \true]);
    }
    /**
     * Generate the HTML for order details.
     *
     * @param WC_Abstract_Order $order The order object.
     *
     * @return string HTML for order details.
     */
    private function getOrderDetailsHtml(WC_Abstract_Order $order): string
    {
        $data = $order->get_data();
        $orderDate = $order->get_date_created();
        $formattedOrderDate = $orderDate ? $orderDate->date('Y-m-d H:i:s') : '?';
        // Payment method details are only available on WC_Order, not on refunds
        $paymentMethodTitle = '';
        $paymentMethod = '';
        $orderTotal = wp_strip_all_tags($order->get_formatted_order_total());
        $billingData = (array) ($data['billing'] ?? []);
        $shippingData = (array) ($data['shipping'] ?? []);
        if ($order instanceof WC_Order) {
            $paymentMethodTitle = (string) $order->get_payment_method_title();
            $paymentMethod = (string) $order->get_payment_method();
        }
        return '<div class="section">
        <table>
            <tr><th>Order ID</th><td>' . esc_html((string) $order->get_id()) . '</td></tr>
            <tr><th>Date Created</th><td>' . esc_html($formattedOrderDate) . '</td></tr>
            <tr><th>Status</th><td>' . esc_html($order->get_status()) . '</td></tr>
            <tr><th>Payment Method</th><td>' . esc_html($paymentMethodTitle) . ' &nbsp; (<code>' . esc_html($paymentMethod) . '</code>)</td></tr>
            <tr><th>Total</th><td>' . esc_html($orderTotal) . '</td></tr>
            <tr><th>Billing</th><td>' . $this->generateAddress($billingData) . '</td></tr>
            <tr><th>Shipping</th><td>' . $this->generateAddress($shippingData) . '</td></tr>
        </table>
    </div>';
    }
    // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh -- intentionally ignoring it for now.
    private function generateAddress(array $address): string
    {
        $firstName = $this->anonymizeValue((string) ($address['first_name'] ?? ''));
        $lastName = $this->anonymizeValue((string) ($address['last_name'] ?? ''));
        $address1 = $this->anonymizeValue((string) ($address['address_1'] ?? ''));
        $city = (string) ($address['city'] ?? '');
        $state = (string) ($address['state'] ?? '');
        $postcode = (string) ($address['postcode'] ?? '');
        $country = (string) ($address['country'] ?? '');
        $email = $this->anonymizeValue((string) ($address['email'] ?? ''));
        $phone = $this->anonymizeValue((string) ($address['phone'] ?? ''));
        $output = [];
        $output[] = trim($firstName . ' ' . $lastName);
        $output[] = $address1;
        $output[] = ($city ? $city . ', ' : '') . ($state ? $state . ' ' : '') . ($postcode ? $postcode . ' ' : '') . ($country ? '/ ' . $country : '');
        if ($email || $phone) {
            $output[] = '---';
            $output[] = 'Email: ' . $email;
            $output[] = 'Phone: ' . $phone;
        }
        $output = array_filter($output);
        return implode('<br/>', $output);
    }
    /**
     * Generate the HTML for order items.
     *
     * @param WC_Abstract_Order $order The order object.
     *
     * @return string HTML for order items.
     */
    private function getOrderItemsHtml(WC_Abstract_Order $order): string
    {
        $html = [];
        $html[] = '<div class="section">
        <h2>Order Items</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>';
        foreach ($order->get_items() as $item) {
            // Some order item types may not have get_total() method
            $itemTotal = 0.0;
            if (method_exists($item, 'get_total')) {
                $itemTotal = (float) $item->get_total();
            }
            $html[] = '<tr>
                <td>' . esc_html($item->get_name()) . '</td>
                <td>' . esc_html((string) $item->get_quantity()) . '</td>
                <td>' . wc_price($order->get_item_subtotal($item, \false, \true)) . '</td>
                <td>' . wc_price($itemTotal) . '</td>
            </tr>';
        }
        $html[] = '</table></div>';
        return implode('', $html);
    }
    /**
     * Generate the HTML for payment details.
     *
     * @param WC_Abstract_Order $order The order object.
     *
     * @return string HTML for payment details.
     */
    private function getPaymentDetailsHtml(WC_Abstract_Order $order): string
    {
        // Get Payoneer-specific payment meta data
        $paymentDetails = $this->getPayoneerPaymentDetails($order);
        if (empty($paymentDetails)) {
            return '';
        }
        $html = [];
        $html[] = '<div class="section">
        <h2>Payment Details</h2>
        <table>';
        foreach ($paymentDetails as $key => $value) {
            $html[] = '<tr>
                <th>' . esc_html((string) $key) . '</th>
                <td>' . esc_html((string) $value) . '</td>
            </tr>';
        }
        $html[] = '</table></div>';
        return implode('', $html);
    }
    /**
     * Generate the HTML for order notes.
     *
     * @param WC_Abstract_Order $order The order object.
     *
     * @return string HTML for order notes.
     */
    private function getOrderNotesHtml(WC_Abstract_Order $order): string
    {
        $notes = wc_get_order_notes(['order_id' => $order->get_id(), 'type' => 'internal']);
        if (empty($notes)) {
            return '';
        }
        $html = [];
        $html[] = '<div class="section">
        <h2>Order Notes</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Note</th>
                <th>Added By</th>
            </tr>';
        foreach ($notes as $note) {
            $html[] = '<tr>
                <td>' . esc_html($note->date_created && is_object($note->date_created) && method_exists($note->date_created, 'date') ? (string) $note->date_created->date('Y-m-d H:i:s') : '') . '</td>
                <td>' . esc_html((string) $note->content) . '</td>
                <td>' . esc_html((string) $note->added_by) . '</td>
            </tr>';
        }
        $html[] = '</table></div>';
        return implode('', $html);
    }
    /**
     * Builds a standardized HTML document with consistent styling.
     *
     *
     * @param array $config Document configuration with the following keys:
     *                     - title: Document title (and h1 heading)
     *                     - body: Main content HTML
     *                     - error: (optional) Whether this is an error document
     *
     * @return string Complete HTML document.
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong -- we want to refactor this later
    private function buildHtmlDocument(array $config): string
    {
        $title = (string) ($config['title'] ?? 'System Report');
        $body = (string) ($config['body'] ?? '');
        $isError = (bool) ($config['error'] ?? \false);
        $emailHeader = sprintf('<strong>%s</strong> | %s', esc_html(get_bloginfo('name')), esc_html($title));
        $emailFooter = sprintf('Generated on <strong>%s</strong> | Payoneer Checkout Version <strong>%s</strong>', date('Y-m-d (H:i:s)'), esc_html($this->pluginVersion));
        $placeholders = ['{BODY_CLASSES}' => $isError ? 'is-error' : '', '{DOCUMENT_TITLE}' => wp_kses_post($emailHeader), '{DOCUMENT_HEADER}' => esc_html($title), '{DOCUMENT_BODY}' => wp_kses_post($body), '{DOCUMENT_FOOTER}' => wp_kses_post($emailFooter)];
        /*
         * Here we use a simplified version of placeholder insertion, instead of using the otherwise
         * common loop: This approach is more efficient and the placeholders are sanitized directly
         * before usage and are not reused in other methods.
         */
        return str_replace(array_keys($placeholders), array_values($placeholders), $this->htmlTemplate);
    }
    /**
     * Anonymize sensitive values based on a standardized pattern.
     *
     * @param string $value The value to anonymize.
     *
     * @return string Anonymized value.
     */
    private function anonymizeValue(string $value): string
    {
        if (empty($value)) {
            return '';
        }
        // Email anonymization pattern
        if (filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            [$username, $domain] = explode('@', $value);
            $anonymizedUsername = (substr($username, 0, 2) ?: '') . str_repeat('*', strlen($username) - 2);
            return $anonymizedUsername . '@' . $domain;
        }
        if (strlen($value) < 6) {
            return (substr($value, 0, 2) ?: '') . str_repeat('*', strlen($value) - 2);
        }
        return (substr($value, 0, 2) ?: '') . str_repeat('*', strlen($value) - 4) . (substr($value, -2) ?: '');
    }
    /**
     * Get Payoneer-specific payment details from order meta.
     *
     * @param WC_Abstract_Order $order The order object.
     *
     * @return array Payment details.
     */
    // phpcs:ignore Inpsyde.CodeQuality.NestingLevel.High
    private function getPayoneerPaymentDetails(WC_Abstract_Order $order): array
    {
        $paymentDetails = [];
        // Meta-data to include in the report.
        $metaKeys = ['_payoneer_payment_transaction_id' => 'Payoneer: Transaction ID', '_wc_order_attribution_device_type' => 'WC: Device Type'];
        // Which items from the List session to include.
        $listKeys = ['identification' => ['longId' => 'List: Long ID', 'shortId' => 'List: Short ID', 'pspId' => 'List: Psp ID', 'transactionId' => 'List: Transaction ID']];
        foreach ($metaKeys as $metaKey => $label) {
            $value = $order->get_meta($metaKey);
            if (empty($value)) {
                continue;
            }
            $paymentDetails[$label] = $value;
        }
        $listSession = $order->get_meta('_payoneer_list_session');
        if (empty($listSession)) {
            return $paymentDetails;
        }
        foreach ($listKeys as $listGroup => $group) {
            foreach ($group as $key => $label) {
                assert(is_array($listSession));
                $value = $listSession[$listGroup][$key] ?? null;
                if ($value === null) {
                    continue;
                }
                $paymentDetails[$label] = $value;
            }
        }
        return $paymentDetails;
    }
}
