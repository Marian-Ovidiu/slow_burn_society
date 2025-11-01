<?php

namespace Hostinger\Reach\Dto;

class Cart {

    private string $hash;
    private string $email;
    private string $currency;
    private array $items;
    private array $totals;

    public function __construct( string $hash, string $email, string $currency, array $items, array $totals ) {
        $this->hash     = $hash;
        $this->email    = $email;
        $this->currency = $currency;
        $this->items    = $items;
        $this->totals   = $totals;
    }

    public function get_hash(): string {
        return $this->hash;
    }

    public function get_currency(): string {
        return $this->currency;
    }

    public function get_email(): string {
        return $this->email;
    }

    public function get_items(): array {
        return array_map(
            function ( $item ) {
                $cart_item = new CartItem( $item['product_id'], $item['quantity'] );

                return $cart_item->to_array();
            },
            $this->items
        );
    }

    public function get_totals(): array {
        $totals = Totals::from_cart_totals( $this->totals, $this->currency );

        return $totals->to_array();
    }

    public function to_array(): array {
        return array(
            'hash'           => $this->get_hash(),
            'currency'       => $this->get_currency(),
            'customer_email' => $this->get_email(),
            'items'          => $this->get_items(),
            'totals'         => $this->get_totals(),
        );
    }

    public static function from_array( array $data ): Cart {
        return new self(
            $data['hash'],
            $data['customer_email'],
            $data['currency'],
            $data['items'],
            $data['totals'],
        );
    }
}
