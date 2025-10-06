<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Mor;

use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
 * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
 */
class MorModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $container) : bool
    {
        $taxCodeFieldName = (string) $container->get('mor.product_tax_code_field_name');
        $taxCodeFormat = (string) $container->get('mor.product_tax_code_format');
        $taxCodeFieldTitle = (string) $container->get('mor.product_tax_code_field_title');
        $this->addTaxCodeFieldToProduct($taxCodeFieldName, $taxCodeFormat, $taxCodeFieldTitle);
        $this->addTaxCodeFieldToVariation($taxCodeFieldName, $taxCodeFormat, $taxCodeFieldTitle);
        $this->registerProductTaxCodeSaving($taxCodeFieldName);
        $this->registerProductVariationTaxCodeSaving($taxCodeFieldName);
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function services() : array
    {
        static $services;
        if ($services === null) {
            $services = (require_once dirname(__DIR__) . '/inc/services.php');
        }
        /** @var callable(): array<string, callable(ContainerInterface $container):mixed> $services */
        return $services();
    }
    /**
     * @inheritDoc
     */
    public function extensions() : array
    {
        static $extensions;
        if ($extensions === null) {
            $extensions = (require_once dirname(__DIR__) . '/inc/extensions.php');
        }
        /** @var callable(): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions();
    }
    /**
     * @param string $taxCodeFieldName
     * @param string $taxCodeFormat
     * @param string $taxCodeFieldTitle
     */
    protected function addTaxCodeFieldToProduct(string $taxCodeFieldName, string $taxCodeFormat, string $taxCodeFieldTitle) : void
    {
        add_action('woocommerce_product_options_general_product_data', static function () use($taxCodeFieldName, $taxCodeFormat, $taxCodeFieldTitle) : void {
            echo '<div class="payoneer-checkout.tax-code options_group">';
            woocommerce_wp_text_input(['id' => $taxCodeFieldName, 'placeholder' => __('Tax code to apply on this product', 'payoneer-checkout'), 'label' => __('Tax code', 'payoneer-checkout'), 'desc_tip' => 'true', 'custom_attributes' => ['pattern' => $taxCodeFormat, 'title' => $taxCodeFieldTitle]]);
            echo '</div>';
        }, 10, 0);
    }
    /**
     * @param string $taxCodeFieldName
     * @param string $taxCodeFormat
     * @param string $taxCodeFieldTitle
     */
    protected function addTaxCodeFieldToVariation(string $taxCodeFieldName, string $taxCodeFormat, string $taxCodeFieldTitle) : void
    {
        add_action(
            'woocommerce_variation_options_pricing',
            /**
             * @psalm-suppress UnusedClosureParam
             */
            static function ($loop, $variationData, $variation) use($taxCodeFieldName, $taxCodeFormat, $taxCodeFieldTitle) : void {
                echo '<div class="payoneer-checkout.tax-code form-row form-row-full">';
                $loop = (string) $loop;
                assert($variation instanceof \WP_Post);
                woocommerce_wp_text_input(['id' => 'variation' . $taxCodeFieldName . '[' . $loop . ']', 'class' => 'short', 'placeholder' => __('Tax code to apply on this product', 'payoneer-checkout'), 'label' => __('Tax Code', 'payoneer-checkout'), 'value' => get_post_meta((int) $variation->ID, $taxCodeFieldName, \true), 'desc_tip' => 'true', 'custom_attributes' => ['pattern' => $taxCodeFormat, 'title' => $taxCodeFieldTitle]]);
                echo '</div>';
            },
            10,
            3
        );
    }
    protected function registerProductTaxCodeSaving(string $taxCodeFieldName) : void
    {
        $callback = static function ($postId) use($taxCodeFieldName) : void {
            //@phpcs:disable WordPress.Security.NonceVerification.Missing
            $taxcode = isset($_POST[$taxCodeFieldName]) && is_string($_POST[$taxCodeFieldName]) ? sanitize_text_field(wp_unslash($_POST[$taxCodeFieldName])) : null;
            if ($taxcode === null) {
                return;
            }
            if ($taxcode === '') {
                delete_post_meta((int) $postId, $taxCodeFieldName);
                return;
            }
            update_post_meta((int) $postId, $taxCodeFieldName, esc_attr($taxcode));
        };
        foreach (['simple', 'variable'] as $productType) {
            add_action("woocommerce_process_product_meta_{$productType}", $callback);
        }
    }
    /**
     * @param string $taxCodeFieldName
     */
    protected function registerProductVariationTaxCodeSaving(string $taxCodeFieldName) : void
    {
        add_action('woocommerce_save_product_variation', static function ($variationId, $i) use($taxCodeFieldName) : void {
            $postedFieldName = 'variation' . $taxCodeFieldName;
            //@phpcs:disable WordPress.Security.NonceVerification.Missing
            /**
             * @psalm-suppress MixedArrayOffset
             */
            $taxCode = isset($_POST[$postedFieldName][$i]) && is_string($_POST[$postedFieldName][$i]) ? sanitize_text_field(wp_unslash($_POST[$postedFieldName][$i])) : null;
            if ($taxCode === null) {
                return;
            }
            if ($taxCode === '') {
                delete_post_meta((int) $variationId, $taxCodeFieldName);
                return;
            }
            update_post_meta((int) $variationId, $taxCodeFieldName, esc_attr($taxCode));
        }, 10, 2);
    }
}
