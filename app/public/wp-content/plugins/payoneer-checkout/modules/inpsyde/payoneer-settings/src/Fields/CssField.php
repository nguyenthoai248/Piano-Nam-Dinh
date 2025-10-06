<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Settings\Fields;

use DOMDocument;
use Syde\Vendor\Inpsyde\PaymentGateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PaymentGateway\SettingsFieldRendererInterface;
class CssField implements SettingsFieldRendererInterface
{
    public function render(string $fieldId, array $fieldConfig, PaymentGateway $gateway): string
    {
        /**
         * Regular esc_textarea (used by 'generate_textarea_html') escapes quotes resulting in
         * an extra layer of escaping whenever we save the field. This is undesirable.
         * To be able to keep using the core method, we simply revert the escaping in this one-time
         * hook
         */
        $escapeOverride = static function (string $safeText) use (&$escapeOverride): string {
            assert(is_callable($escapeOverride));
            remove_filter('esc_textarea', $escapeOverride);
            return stripslashes($safeText);
        };
        add_filter('esc_textarea', $escapeOverride);
        $base = $gateway->generate_textarea_html($fieldId, $fieldConfig);
        if (empty($base)) {
            return $base;
        }
        if (isset($fieldConfig['custom_attributes']['readonly'])) {
            return $base;
        }
        $dom = new DOMDocument();
        $dom->loadHtml($base);
        $fieldsetNode = $dom->getElementsByTagName('fieldset')->item(0);
        if (!$fieldsetNode) {
            return $base;
        }
        $inputId = $gateway->get_field_key($fieldId);
        $fieldsetNode->appendChild($this->createButtonElement($dom, $inputId, $fieldConfig));
        return $dom->saveHTML();
    }
    protected function createButtonElement(DOMDocument $dom, string $inputId, array $fieldConfig): \DOMNode
    {
        $element = $dom->createElement('button', 'Reset');
        $element->setAttribute('data-target', esc_attr('#' . $inputId));
        $element->setAttribute('data-default', esc_attr((string) $fieldConfig['default']));
        return $element;
    }
}
