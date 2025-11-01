<?php

namespace Hostinger\Reach\Repositories;

use Exception;
use Hostinger\Reach\Admin\Database\ContactListsTable;
use Hostinger\Reach\Models\ContactList;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ContactListRepository extends Repository {

    public function __construct( wpdb $db, ContactListsTable $table ) {
        parent::__construct( $db );
        $this->table = $table;
    }

    public function all( array $where = array() ): array {
        $limit   = apply_filters( 'hostinger_reach_contact_lists_limit', 100 );
        $query   = $this->build_query( $where, $limit );
        $results = $this->db->get_results( $query, ARRAY_A );

        $contact_lists = array();
        foreach ( $results as $result ) {
            $contact_lists[] = ( new ContactList( $result ) )->to_array();
        }

        return $contact_lists;
    }

    public function exists( string $group_name ): bool {
        try {
            $this->get( $group_name );
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function get( string $group_name ): array {
        $query   = $this->db->prepare( 'SELECT * FROM %i WHERE name = %s', $this->table->table_name(), $group_name );
        $results = $this->db->get_results( $query, ARRAY_A );
        if ( ! empty( $results ) ) {
            return ( new ContactList( $results[0] ) )->to_array();
        }

        throw new Exception( 'Contact List not found' );
    }

    public function insert( array $fields ): bool {
        if ( $this->exists( $fields['name'] ) ) {
            return false;
        }

        return $this->db->insert( $this->table->table_name(), $fields );
    }

    public function update( array $fields ): bool {
        if ( ! isset( $fields['id'] ) ) {
            return false;
        }

        $data = array_diff_key( $fields, array_flip( array( 'id' ) ) );
        if ( empty( $data ) ) {
            return false;
        }

        return $this->db->update(
            $this->table->table_name(),
            $data,
            array( 'id' => $fields['id'] )
        );
    }
}
