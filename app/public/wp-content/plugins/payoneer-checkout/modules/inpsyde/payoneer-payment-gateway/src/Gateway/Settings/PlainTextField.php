<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
/**
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
 * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
 * @psalm-suppress MissingParamType
 */
class PlainTextField implements SettingsFieldRendererInterface
{
    public function render(string $fieldId, array $fieldConfig, PaymentGateway $gateway) : string
    {
        $fieldKey = $gateway->get_field_key($fieldId);
        $data = array_merge(['title' => '', 'disabled' => \false, 'class' => '', 'css' => '', 'placeholder' => '', 'type' => 'text', 'desc_tip' => \false, 'description' => '', 'custom_attributes' => []], $fieldConfig);
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php 
        echo esc_attr($fieldKey);
        ?>">
                    <?php 
        echo wp_kses_post((string) $data['title']);
        ?>
                    <?php 
        echo $gateway->get_tooltip_html($data);
        ?>
                </label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text">
                        <span><?php 
        echo wp_kses_post((string) $data['title']);
        ?></span>
                    </legend>
                    <?php 
        echo $gateway->get_description_html($data);
        ?>
                </fieldset>
            </td>
        </tr>
        <?php 
        return (string) ob_get_clean();
    }
}
