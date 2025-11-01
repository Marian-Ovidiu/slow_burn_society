<?php

namespace Hostinger\Reach\Api\Webhooks\Handlers;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;

use Hostinger\Reach\Dto\CartItem;
use Hostinger\Reach\Dto\Totals;
use WC_Order;

class OrderPurchased extends WebhookHandler {
    const WEBHOOK_NAME = 'order.purchased';

    private IntegrationsApiHandler $integrations_api_handler;

    public function __construct( ReachApiHandler $reach_api_handler, IntegrationsApiHandler $integrations_api_handler ) {
        parent::__construct( $reach_api_handler );
        $this->integrations_api_handler = $integrations_api_handler;
    }

    public function get_name(): string {
        return self::WEBHOOK_NAME;
    }

    public function get_metadata( mixed $data ): array {
        if ( ! $data instanceof WC_Order ) {
            return array();
        }

        return array(
            'order_id'        => $data->get_id(),
            'order_number'    => $data->get_order_number(),
            'totals'          => $this->get_order_totals( $data ),
            'items'           => $this->get_order_items( $data ),
            'purchase_date'   => $this->get_purchase_date( $data ),
            'payment_method'  => $data->get_payment_method(),
            'shipping_method' => $this->get_shipping_method_code( $data ),
            'customer'        => $this->get_customer_data( $data ),
            'currency'        => $data->get_currency(),
            'type'            => $data->get_type(),
        );
    }

    public function init_hooks(): void {
        add_action(
            'woocommerce_order_status_processing',
            array(
                $this,
                'handle_woocommerce_order_status_processing',
            ),
            10,
            2
        );
    }

    public function handle_woocommerce_order_status_processing( int $order_id ): void {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $email = $order->get_billing_email();

        if ( ! $email ) {
            return;
        }

        $this->handle( $email, $order );
    }

    public function is_enabled(): bool {
        return parent::is_enabled() && $this->integrations_api_handler->is_active( 'woocommerce' );
    }

    private function get_purchase_date( WC_Order $order ): ?string {
        $date_created = $order->get_date_created();

        return $date_created?->format( 'Y-m-d\TH:i:s\Z' );
    }

    private function get_order_totals( WC_Order $order ): array {
        $totals = Totals::from_order( $order );

        return $totals->to_array();
    }

    private function get_shipping_method_code( WC_Order $order ): ?string {
        $shipping_methods = $order->get_shipping_methods();

        if ( empty( $shipping_methods ) ) {
            return null;
        }

        $shipping_method = reset( $shipping_methods );

        return $shipping_method->get_method_id();
    }

    private function get_order_items( WC_Order $order ): array {
        $items = array();

        foreach ( $order->get_items() as $item ) {
            $cart_item = new CartItem( $item->get_product_id(), $item->get_quantity() );
            $items[]   = $cart_item->to_array();
        }

        return $items;
    }

    private function get_customer_data( WC_Order $order ): array {
        return array(
            'email'   => $order->get_billing_email(),
            'name'    => $order->get_billing_first_name(),
            'surname' => $order->get_billing_last_name(),
            'phone'   => $order->get_billing_phone(),
            'company' => $order->get_billing_company(),
            'address' => array(
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city'      => $order->get_billing_city(),
                'state'     => $order->get_billing_state(),
                'postcode'  => $order->get_billing_postcode(),
                'country'   => $order->get_billing_country(),
            ),
        );
    }
}
