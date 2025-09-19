<?php

namespace Hostinger\Reach\Models;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ContactList implements Model {
    private int $id = 0;
    private string $name;


    public function __construct( array $db_array = array() ) {
        if ( isset( $db_array['id'] ) ) {
            $this->set_id( $db_array['id'] );
        }

        if ( isset( $db_array['name'] ) ) {
            $this->set_name( $db_array['name'] );
        }
    }

    public function get_id(): int {
        return $this->id;
    }

    public function set_id( int $id ): void {
        $this->id = $id;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function set_name( string $name ): void {
        $this->name = sanitize_text_field( trim( $name ) );
    }


    public function to_array(): array {
        return array(
            'id'   => $this->get_id(),
            'name' => $this->get_name(),
        );
    }
}
