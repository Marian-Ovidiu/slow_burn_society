<?php

namespace Hostinger\Reach\Tracking;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Integrations\WooCommerce\WooCommerceIntegration;
use Hostinger\Reach\Models\Cart;
use Hostinger\Reach\Repositories\CartRepository;
use WC_Cart;

class AbandonedCarts {

    private CartRepository $cart_repository;
    private ReachApiHandler $reach_api_handler;
    private IntegrationsApiHandler $integrations_api_handler;
    private WC_Cart|null $cart = null;
    private string|null $hash  = null;

    public function __construct( CartRepository $cart_repository, ReachApiHandler $reach_api_handler, IntegrationsApiHandler $integrations_api_handler ) {
        $this->cart_repository          = $cart_repository;
        $this->reach_api_handler        = $reach_api_handler;
        $this->integrations_api_handler = $integrations_api_handler;
    }

    public function init(): void {
        add_action(
            'woocommerce_store_api_cart_update_order_from_request',
            array(
                $this,
                'track_abandoned_cart',
            ),
            99
        );
        add_action( 'woocommerce_cart_item_set_quantity', array( $this, 'track_abandoned_cart' ), 99 );
        add_action( 'woocommerce_add_to_cart', array( $this, 'track_abandoned_cart' ), 99 );
        add_action( 'woocommerce_after_calculate_totals', array( $this, 'track_abandoned_cart' ), 99 );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'track_abandoned_cart' ), 99 );
        add_action( 'woocommerce_cart_item_restored', array( $this, 'track_abandoned_cart' ), 99 );
        add_action( 'woocommerce_thankyou', array( $this, 'clear_cart' ), 99 );
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'clear_cart' ), 99 );
    }

    public function track_abandoned_cart(): void {
        if (
            ! $this->reach_api_handler->is_connected()
            || ! $this->integrations_api_handler->is_active( WooCommerceIntegration::INTEGRATION_NAME )
        ) {
            return;
        }

        $cart = WC()->cart;

        if ( $cart ) {
            $this->cart = $cart;
            $this->hash = $this->get_cart_hash();
        }

        if ( $this->hash ) {
            $this->update_or_delete_cart();
        } else {
            $this->maybe_create_cart();
        }
    }

    public function clear_cart(): void {
        $this->hash = $this->get_cart_hash();
        if ( $this->hash ) {
            $this->cart_repository->delete( $this->hash );
        }
    }

    private function update_or_delete_cart(): void {
        $fields = $this->get_cart_fields();
        if ( $this->cart->is_empty() ) {
            $this->cart_repository->delete( $this->hash );
        } else {
            $this->cart_repository->update( $fields );
        }
    }

    private function maybe_create_cart(): void {
        if ( $this->cart->is_empty() ) {
            return;
        }

        $this->set_cart_hash();
        $fields = $this->get_cart_fields();
        if ( empty( $fields ) ) {
            return;
        }
        $this->cart_repository->insert( $fields );
    }

    private function get_cart_fields(): array {
        $customer = WC()->customer;

        if ( ! $customer || ! $this->cart || ! $this->hash ) {
            return array();
        }

        $email = $customer->get_email();

        if ( ! $email || ! $customer->get_id() ) {
            return array();
        }

        return array(
            'hash'           => $this->hash,
            'customer_id'    => $customer->get_id(),
            'customer_email' => $email,
            'totals'         => wp_json_encode( $this->cart->get_totals() ),
            'items'          => $this->get_cart_items(),
            'updated_at'     => current_time( 'mysql' ),
            'status'         => Cart::STATUS_ACTIVE,
            'currency'       => get_woocommerce_currency(),
        );
    }

    private function get_cart_items(): string {
        $cart_data = array();

        if ( $this->cart ) {
            foreach ( $this->cart->get_cart_contents() as $item ) {
                $cart_data[] = $this->cart_repository->get_cart_item( $item );
            }
        }

        return wp_json_encode( $cart_data );
    }

    private function get_cart_hash(): ?string {
        $customer = WC()->customer;
        if ( $customer ) {
            $cart = $this->cart_repository->get_by_customer_id( $customer->get_id() );

            return $cart['hash'] ?? null;
        }

        return null;
    }

    private function set_cart_hash(): void {
        $this->hash = wp_generate_uuid4();
    }
}
