<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\Modifier;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
use WC_Cart;
use WC_Order;
abstract class AbstractTaxModifier
{
    /** @var WC_Order|null */
    protected $order = null;
    /** @var WC_Cart|null */
    protected $cart = null;
    /**
     * @param WC_Order | WC_Cart $instance
     */
    public function __construct($instance)
    {
        if (is_a($instance, WC_Cart::class)) {
            $this->cart = $instance;
            return;
        }
        if (is_a($instance, WC_Order::class)) {
            $this->order = $instance;
        }
    }
    /**
     * @var TaxModifierItem[]
     */
    protected $modifiers = [];
    public function push(TaxModifierItem $modifier) : void
    {
        $this->modifiers[] = $modifier;
    }
    protected function overwriteOrderTaxes(string $newTax) : ?array
    {
        return [TaxesModule::TAX_RATE_ID => $newTax];
    }
    public abstract function modify() : void;
}
