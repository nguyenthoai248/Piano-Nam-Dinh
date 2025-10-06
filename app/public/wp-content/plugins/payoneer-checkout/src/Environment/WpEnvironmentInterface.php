<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Environment;

/**
 * Represents WordPress environment.
 */
interface WpEnvironmentInterface
{
    /**
     * Return current version of PHP.
     *
     * @return string
     */
    public function getPhpVersion(): string;
    /**
     * Return current version of WordPress.
     *
     * @return string
     */
    public function getWpVersion(): string;
    /**
     * Return current version of WooCommerce, empty string if not installed.
     *
     * @return string
     */
    public function getWcVersion(): string;
    /**
     * Return true if WooCommerce plugin is active, false otherwise.
     *
     * @return bool
     */
    public function getWcActive(): bool;
}
