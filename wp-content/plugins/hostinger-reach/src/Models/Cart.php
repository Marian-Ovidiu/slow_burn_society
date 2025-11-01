<?php

namespace Hostinger\Reach\Models;


if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Cart implements Model {

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_OLD       = 'old';

    public const STATUSES = array(
        self::STATUS_ACTIVE,
        self::STATUS_ABANDONED,
        self::STATUS_OLD,
    );

    private string $hash      = '';
    private ?int $customer_id = null;
    private string $email     = '';
    private array $items      = array();
    private array $totals     = array();
    private string $currency;
    private string $updated_at = '';
    private string $status     = self::STATUS_ACTIVE;

    public function __construct( array $db_array = array() ) {

        if ( array_key_exists( 'hash', $db_array ) ) {
            $this->set_hash( (string) $db_array['hash'] );
        }

        if ( array_key_exists( 'customer_id', $db_array ) ) {
            $this->set_customer_id( $db_array['customer_id'] === null ? null : (int) $db_array['customer_id'] );
        }

        if ( array_key_exists( 'customer_email', $db_array ) ) {
            $this->set_email( (string) $db_array['customer_email'] );
        }

        if ( array_key_exists( 'items', $db_array ) ) {
            $this->set_items( $db_array['items'] );
        }

        if ( array_key_exists( 'totals', $db_array ) ) {
            $this->set_totals( $db_array['totals'] );
        }

        if ( array_key_exists( 'updated_at', $db_array ) ) {
            $this->set_updated_at( (string) $db_array['updated_at'] );
        }

        if ( array_key_exists( 'currency', $db_array ) ) {
            $this->set_currency( (string) $db_array['currency'] );
        } else {
            $this->set_currency( get_woocommerce_currency() );
        }

        if ( array_key_exists( 'status', $db_array ) ) {
            $this->set_status( $db_array['status'] );
        }
    }

    public function get_hash(): string {
        return $this->hash;
    }

    public function set_hash( string $hash ): void {
        $this->hash = sanitize_text_field( trim( $hash ) );
    }

    public function get_customer_id(): ?int {
        return $this->customer_id;
    }

    public function set_customer_id( ?int $customer_id ): void {
        $this->customer_id = $customer_id;
    }

    public function get_email(): string {
        return $this->email;
    }

    public function set_email( string $email ): void {
        $this->email = sanitize_email( $email );
    }

    public function get_items(): array {
        return $this->items;
    }

    public function set_items( mixed $items ): void {
        if ( is_string( $items ) ) {
            $decoded = json_decode( $items, true );
            if ( is_array( $decoded ) ) {
                $this->items = $decoded;

                return;
            }
        }

        $this->items = is_array( $items ) ? $items : array();
    }

    public function get_totals(): array {
        return $this->totals;
    }

    public function set_totals( mixed $totals ): void {
        if ( is_string( $totals ) ) {
            $decoded = json_decode( $totals, true );
            if ( is_array( $decoded ) ) {
                $this->totals = $decoded;

                return;
            }
        }

        $this->totals = is_array( $totals ) ? $totals : array();
    }

    public function get_currency(): string {
        return $this->currency;
    }

    public function set_currency( string $currency ): void {
        $this->currency = $currency;
    }

    public function get_updated_at(): string {
        return $this->updated_at;
    }

    public function set_updated_at( string $updated_at ): void {
        $this->updated_at = sanitize_text_field( trim( $updated_at ) );
    }

    public function update(): void {
        $this->set_updated_at( current_time( 'mysql' ) );
    }

    public function get_status(): string {
        return $this->status;
    }

    public function set_status( string $status ): void {
        if ( array_key_exists( $status, self::STATUSES ) ) {
            $this->status = $status;
        }
    }

    public function to_array(): array {
        return array(
            'hash'           => $this->get_hash(),
            'currency'       => $this->get_currency(),
            'customer_id'    => $this->get_customer_id(),
            'customer_email' => $this->get_email(),
            'items'          => $this->get_items(),
            'totals'         => $this->get_totals(),
            'updated_at'     => $this->get_updated_at(),
            'status'         => $this->get_status(),
        );
    }
}
