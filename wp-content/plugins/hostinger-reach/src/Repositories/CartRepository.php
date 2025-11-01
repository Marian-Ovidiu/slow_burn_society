<?php

namespace Hostinger\Reach\Repositories;

use Exception;
use Hostinger\Reach\Admin\Database\CartsTable;
use Hostinger\Reach\Models\Cart;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class CartRepository extends Repository {

    public const ABANDONED_CART_TIME_IN_SECONDS = HOUR_IN_SECONDS;

    public function __construct( wpdb $db, CartsTable $table ) {
        parent::__construct( $db );
        $this->table = $table;
    }

    public function all( array $where = array(), int $limit = 100 ): array {
        $query   = $this->build_query( $where, $limit );
        $results = $this->db->get_results( $query, ARRAY_A );

        $carts = array();
        foreach ( $results as $result ) {
            $carts[] = ( new Cart( $result ) )->to_array();
        }

        return $carts;
    }

    public function active_abandoned_carts( int $limit = 100, array $columns = array() ): array {
        $abandoned_threshold = time() - self::ABANDONED_CART_TIME_IN_SECONDS;
        $abandoned_date      = gmdate( 'Y-m-d H:i:s', $abandoned_threshold );

        $where_conditions = array(
            'status'     => Cart::STATUS_ACTIVE,
            'updated_at' => array(
                'operator' => '<',
                'value'    => $abandoned_date,
            ),
        );

        $carts = $this->all( $where_conditions, $limit );

        if ( empty( $columns ) ) {
            return $carts;
        }

        return array_map(
            function ( $cart ) use ( $columns ) {
                return array_intersect_key( $cart, array_flip( $columns ) );
            },
            $carts
        );
    }

    public function exists( string $hash ): bool {
        try {
            $this->get( $hash );

            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function get( string $hash ): array {
        $query   = $this->db->prepare( 'SELECT * FROM %i WHERE hash = %s', $this->table->table_name(), $hash );
        $results = $this->db->get_results( $query, ARRAY_A );
        if ( ! empty( $results ) ) {
            return ( new Cart( $results[0] ) )->to_array();
        }

        throw new Exception( 'Cart not found' );
    }

    public function get_by_customer_id( int $customer_id ): array {
        $carts = $this->all( array( 'customer_id' => $customer_id ) );
        if ( ! empty( $carts ) ) {
            return $carts[0];
        }

        return array();
    }

    public function insert( array $fields ): bool {

        if ( ! isset( $fields['hash'] ) ) {
            return false;
        }

        if ( $this->exists( $fields['hash'] ) ) {
            return false;
        }

        return $this->db->insert( $this->table->table_name(), $fields );
    }

    public function delete( string $hash ): bool {
        if ( ! $this->exists( $hash ) ) {
            return false;
        }

        return $this->db->delete( $this->table->table_name(), array( 'hash' => $hash ) );
    }

    public function update( array $fields ): bool {
        if ( ! isset( $fields['hash'] ) ) {
            return false;
        }

        $data = array_diff_key( $fields, array_flip( array( 'hash' ) ) );
        if ( empty( $data ) ) {
            return false;
        }

        $has_changes = ! empty( $this->get_cart_diff( $fields['hash'], $data ) );
        if ( ! $has_changes ) {
            return false;
        }

        return $this->db->update(
            $this->table->table_name(),
            $data,
            array( 'hash' => $fields['hash'] )
        );
    }

    public function get_cart_diff( string $hash, array $data ): array {
        $changes = array();
        try {
            $current_data = $this->get( $hash );
            foreach ( $data as $key => $value ) {
                if ( $key === 'hash' || $key === 'updated_at' ) {
                    continue;
                }

                if ( ! isset( $current_data[ $key ] ) ) {
                    $changes[ $key ] = $value;
                    continue;
                }

                if ( is_array( $current_data[ $key ] ) ) {
                    $current_value = wp_json_encode( $current_data[ $key ] );
                } else {
                    $current_value = $current_data[ $key ];
                }

                if ( $current_value !== $value ) {
                    $changes[ $key ] = $value;
                }
            }

            return $changes;
        } catch ( Exception $e ) {
            return $changes;
        }
    }

    public function get_cart_item( array $item ): array {
        return array(
            'product_id' => $item['variation_id'] ?: $item['product_id'],
            'quantity'   => $item['quantity'],
        );
    }

    public function set_status( string $hash, string $status ): bool {
        if ( ! in_array( $status, Cart::STATUSES, true ) ) {
            return false;
        }

        try {
            return $this->update(
                array(
                    'hash'   => $hash,
                    'status' => $status,
                )
            );
        } catch ( Exception $e ) {
            return false;
        }
    }
}
