<?php

namespace Hostinger\Reach\Dto;

use WC_Order;

class Totals {

    private float $total;
    private float $subtotal;
    private float $tax_total;
    private float $shipping_total;
    private float $discount_total;
    private string $currency;

    public function __construct( float $total, float $subtotal, float $tax_total, float $shipping_total, float $discount_total, string $currency ) {
        $this->total          = $total;
        $this->subtotal       = $subtotal;
        $this->tax_total      = $tax_total;
        $this->shipping_total = $shipping_total;
        $this->discount_total = $discount_total;
        $this->currency       = $currency;
    }

    public function to_array(): array {
        return array(
            'total'          => $this->total,
            'subtotal'       => $this->subtotal,
            'tax_total'      => $this->tax_total,
            'shipping_total' => $this->shipping_total,
            'discount_total' => $this->discount_total,
            'currency'       => $this->currency,
        );
    }

    public static function from_order( WC_Order $order ): self {
        return new self(
            $order->get_total(),
            $order->get_subtotal(),
            $order->get_total_tax(),
            (float) $order->get_shipping_total(),
            (float) $order->get_discount_total(),
            $order->get_currency()
        );
    }


    public static function from_cart_totals( array $cart_totals, string $currency ): self {
        return new self(
            (float) $cart_totals['total'],
            (float) $cart_totals['subtotal'],
            (float) $cart_totals['total_tax'],
            (float) $cart_totals['shipping_total'],
            (float) $cart_totals['discount_total'],
            $currency
        );
    }
}
