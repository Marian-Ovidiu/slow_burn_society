<?php

namespace Hostinger\Reach\Models;

use Hostinger\Reach\Integrations\Reach\ReachFormIntegration;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Form implements Model {
    private string $form_id;
    private string|null $form_title = null;
    private int $contact_list_id    = 0;
    private string $type;
    private WP_Post|null $post = null;
    private bool $is_active;
    private int $submissions = 0;


    public function __construct( array $db_array = array() ) {
        if ( isset( $db_array['form_id'] ) ) {
            $this->set_form_id( $db_array['form_id'] );
        }

        $this->set_type( $db_array['type'] ?? ReachFormIntegration::INTEGRATION_NAME );

        $this->set_is_active( $db_array['is_active'] ?? true );

        if ( isset( $db_array['post_id'] ) ) {
            $this->set_post( $db_array['post_id'] );
        }

        if ( isset( $db_array['form_title'] ) ) {
            $this->set_form_title( $db_array['form_title'] );
        }

        if ( isset( $db_array['submissions'] ) ) {
            $this->set_submissions( $db_array['submissions'] );
        }

        if ( isset( $db_array['contact_list_id'] ) ) {
            $this->set_contact_list_id( $db_array['contact_list_id'] );
        }
    }

    public function get_form_id(): string {
        return $this->form_id;
    }

    public function set_type( string $type ): void {
        $this->type = $type;
    }

    public function get_type(): string {
        return $this->type;
    }

    public function set_form_title( string $form_title ): void {
        $this->form_title = $form_title;
    }

    public function get_form_title(): string|null {
        return $this->form_title;
    }

    public function set_form_id( string $form_id ): void {
        $this->form_id = $form_id;
    }

    public function get_is_active(): bool {
        return $this->is_active;
    }

    public function set_is_active( bool $is_active ): void {
        $this->is_active = $is_active;
    }

    public function get_post(): WP_Post|null {
        return $this->post;
    }

    public function set_post( int $post_id ): void {
        $post = get_post( $post_id, OBJECT );
        if ( $post instanceof WP_Post ) {
            $this->post = $post;
        }
    }

    public function set_contact_list_id( int $id ): void {
        $this->contact_list_id = $id;
    }

    public function get_contact_list_id(): int {
        return $this->contact_list_id;
    }

    public function get_submissions(): int {
        return $this->submissions;
    }

    public function set_submissions( int $submissions ): void {
        $this->submissions = $submissions;
    }

    public function to_array(): array {
        return array(
            'form_id'         => $this->get_form_id(),
            'form_title'      => $this->get_form_title(),
            'is_active'       => $this->get_is_active(),
            'type'            => $this->get_type(),
            'contact_list_id' => $this->get_contact_list_id(),
            'post'            => $this->get_post()?->to_array(),
            'submissions'     => $this->get_submissions(),
        );
    }
}
