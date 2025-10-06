<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\Config;

use InvalidArgumentException;
class StylesColor implements StylesConfig
{
    public const PRIMARY = 'primary';
    public const PRIMARY_TEXT = 'primaryText';
    public const ERROR_HIGHLIGHT = 'errorHighlight';
    public const PAGE_TEXT = 'pageText';
    public const INPUT_TEXT = 'inputText';
    public const INPUT_BACKGROUND = 'inputBackground';
    public const INPUT_BORDER = 'inputBorder';
    public const OPTIONS = [self::PRIMARY, self::PRIMARY_TEXT, self::ERROR_HIGHLIGHT, self::PAGE_TEXT, self::INPUT_TEXT, self::INPUT_BACKGROUND, self::INPUT_BORDER];
    public static function title(string $value): string
    {
        $titles = [self::PRIMARY => __('Primary Button Color', 'payoneer-checkout'), self::PRIMARY_TEXT => __('Primary Button Text Color', 'payoneer-checkout'), self::ERROR_HIGHLIGHT => __('Error Highlight Color', 'payoneer-checkout'), self::PAGE_TEXT => __('Page Text Color', 'payoneer-checkout'), self::INPUT_TEXT => __('Input Text Color', 'payoneer-checkout'), self::INPUT_BACKGROUND => __('Input Background Color', 'payoneer-checkout'), self::INPUT_BORDER => __('Input Border Color', 'payoneer-checkout')];
        if (!isset($titles[$value])) {
            throw new InvalidArgumentException('Invalid value supplied');
        }
        return $titles[$value];
    }
    public static function description(string $value): string
    {
        $descriptions = [self::PRIMARY => __('Sets the background color of primary buttons', 'payoneer-checkout'), self::PRIMARY_TEXT => __('Determines the text color of primary buttons', 'payoneer-checkout'), self::ERROR_HIGHLIGHT => __('Used for highlighting errors throughout the payment components', 'payoneer-checkout'), self::PAGE_TEXT => __('Applies to all text displayed between payment components, above the page background', 'payoneer-checkout'), self::INPUT_TEXT => __('Defines the color of text inside input fields', 'payoneer-checkout'), self::INPUT_BACKGROUND => __('Sets the background color inside input fields', 'payoneer-checkout'), self::INPUT_BORDER => __('Specifies the border color around input fields when they are not focused and not displaying an error', 'payoneer-checkout')];
        if (!isset($descriptions[$value])) {
            throw new InvalidArgumentException('Invalid value supplied');
        }
        return $descriptions[$value];
    }
}
