<?php

namespace Hostinger\Reach\Repositories;

use Exception;
use Hostinger\Reach\Admin\Database\FormsTable;
use Hostinger\Reach\Models\Form;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class FormRepository extends Repository {
    public function __construct( wpdb $db, FormsTable $table ) {
        parent::__construct( $db );
        $this->table = $table;
    }

    public function all( array $where = array() ): array {
        $limit   = apply_filters( 'hostinger_reach_forms_limit', 100 );
        $query   = $this->build_query( $where, $limit );
        $results = $this->db->get_results( $query, ARRAY_A );

        $forms = array();
        foreach ( $results as $result ) {
            $forms[] = ( new Form( $result ) )->to_array();
        }

        return $forms;
    }

    public function exists( string $id ): bool {
        try {
            $this->get( $id );

            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    public function insert( array $fields ): bool {
        if ( $this->exists( $fields['form_id'] ) ) {
            return false;
        }

        return $this->db->insert( $this->table->table_name(), $fields );
    }

    /**
     * @throws Exception
     */
    public function get( string $id ): array {
        $query   = $this->db->prepare( 'SELECT * FROM %i WHERE form_id = %s', $this->table->table_name(), $id );
        $results = $this->db->get_results( $query, ARRAY_A );
        if ( ! empty( $results ) ) {
            return ( new Form( $results[0] ) )->to_array();
        }

        throw new Exception( 'Form not found' );
    }

    public function update( array $fields ): bool {
        if ( ! isset( $fields['form_id'] ) ) {
            return false;
        }

        $data = array_diff_key( $fields, array_flip( array( 'form_id' ) ) );
        if ( empty( $data ) ) {
            return false;
        }

        return $this->db->update(
            $this->table->table_name(),
            $data,
            array( 'form_id' => $fields['form_id'] )
        );
    }

    public function delete( string $form_id ): bool {
        if ( empty( $form_id ) ) {
            return false;
        }

        return (bool) $this->db->delete(
            $this->table->table_name(),
            array( 'form_id' => $form_id )
        );
    }

    public function delete_all_from_post( int $post_id, string $type ): bool {
        if ( empty( $type ) ) {
            return false;
        }

        return (bool) $this->db->delete(
            $this->table->table_name(),
            array(
                'post_id' => $post_id,
                'type'    => $type,
            )
        );
    }

    /**
     * @throws Exception
     */
    public function submit( array $data ): bool {
        if ( empty( $data['form_id'] ) ) {
            return false;
        }

        try {
            $form_id = $data['form_id'];
            $form    = $this->get( $form_id );

            return $this->update(
                array(
                    'form_id'     => $form_id,
                    'submissions' => $form['submissions'] + 1,
                )
            );
        } catch ( Exception $e ) {
            return false;
        }
    }

    public function is_form_active( string $form_id ): bool {
        if ( empty( $form_id ) ) {
            return false;
        }

        try {
            $form = $this->get( $form_id );
            return $form['is_active'];
        } catch ( Exception $e ) {
            return false;
        }
    }
}
