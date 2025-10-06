<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AdminBanner;

class AdminBannerRenderer implements AdminBannerRendererInterface
{
    /**
     * @var string
     */
    protected $bannerId;
    /**
     * @var string
     */
    protected $phoneImageUrl;
    /**
     * @var string
     */
    protected $registerUrl;
    /**
     * @var string
     */
    protected $configureUrl;
    /**
     * @param string $bannerId Id attribute of the banner we are going to render.
     */
    public function __construct(string $bannerId, string $phoneImageUrl, string $registerUrl, string $configureUrl)
    {
        $this->bannerId = $bannerId;
        $this->phoneImageUrl = $phoneImageUrl;
        $this->registerUrl = $registerUrl;
        $this->configureUrl = $configureUrl;
    }
    /**
     * @inheritDoc
     * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function renderBanner(): void
    {
        echo sprintf('<div class="notice" id="%1$s">%2$s</div>', esc_attr($this->bannerId), $this->renderBannerMarkup());
    }
    protected function renderBannerMarkup(): string
    {
        ob_start();
        ?>
        <div class="pn-banner-container">
            <div class="pn-banner">
                <div class="pn-cta">
                    <div class="pn-logotype"></div>
                    <div class="pn-heading">
                        Getting started with <span class="pn-gradient-text">Payoneer Checkout</span>
                    </div>
                    <p>
                        <?php 
        esc_html_e('Ready to convert more customers and deliver checkout experiences that build
                        loyalty?', 'payoneer-checkout');
        ?><br/>
                        <?php 
        esc_html_e('Register for your Payoneer Checkout account and start selling around the
                        world.', 'payoneer-checkout');
        ?>
                    </p>
                    <div class="pn-buttons">
                        <a href="<?php 
        echo esc_url($this->registerUrl);
        ?>" class="pn-button-gradient"
                           target="blank">
                            <?php 
        esc_html_e('Register for Checkout', 'payoneer-checkout');
        ?>
                        </a>
                        <a href="<?php 
        echo esc_url($this->configureUrl);
        ?>" class="pn-button-regular">
                            <?php 
        esc_html_e('Configure Checkout plugin', 'payoneer-checkout');
        ?>

                        </a>
                    </div>
                </div>

            </div>
            <div class="pn-background">
                <div class="pn-arc-bg">
                </div>
                <div class="pn-decoration">
                    <img src="<?php 
        echo esc_url($this->phoneImageUrl);
        ?>" class="pn-phone">
                </div>
            </div>
        </div>
        <?php 
        return (string) ob_get_clean();
    }
}
