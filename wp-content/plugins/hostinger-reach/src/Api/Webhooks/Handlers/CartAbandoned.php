<?php

namespace Hostinger\Reach\Api\Webhooks\Handlers;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Models\Cart;
use Hostinger\Reach\Dto\Cart as CartDto;
use Hostinger\Reach\Repositories\CartRepository;
use WC_Customer;
use Exception;

class CartAbandoned extends WebhookHandler {
    const WEBHOOK_NAME = 'cart.abandoned';

    private CartRepository $cart_repository;
    private IntegrationsApiHandler $integrations_api_handler;

    public function __construct( ReachApiHandler $reach_api_handler, IntegrationsApiHandler $integrations_api_handler, CartRepository $cart_repository ) {
        parent::__construct( $reach_api_handler );
        $this->integrations_api_handler = $integrations_api_handler;
        $this->cart_repository          = $cart_repository;
    }

    public function get_name(): string {
        return self::WEBHOOK_NAME;
    }

    public function get_metadata( mixed $data ): array {
        if ( ! $data instanceof CartDto ) {
            return array();
        }

        return $data->to_array();
    }

    public function send( string $cart_hash ): void {
        if ( ! $this->is_enabled() ) {
            return;
        }

        try {
            $cart  = $this->cart_repository->get( $cart_hash );
            $email = $this->get_customer_email( $cart );
            if ( $email ) {
                $result = $this->handle( $email, CartDto::from_array( $cart ) );
                if ( $result ) {
                    $this->cart_repository->set_status( $cart_hash, Cart::STATUS_ABANDONED );
                }
            }
        } catch ( Exception $e ) {
            return;
        }
    }

    public function get_customer_email( array $cart ): string {
        if ( isset( $cart['customer_email'] ) && $cart['customer_email'] ) {
            return $cart['customer_email'];
        }

        $customer_id = $cart['customer_id'] ?? null;

        if ( $customer_id > 0 ) {
            $customer = new WC_Customer( $customer_id );
            return $customer->get_email();
        }

        return '';
    }

    public function is_enabled(): bool {
        return parent::is_enabled() && $this->integrations_api_handler->is_active( 'woocommerce' );
    }

    public function init_hooks(): void {
        return;
    }
}
