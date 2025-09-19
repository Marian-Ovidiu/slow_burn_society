<?php

namespace Hostinger\Reach\Integrations;

use Hostinger\Reach\Models\Form;
use Exception;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

abstract class Integration {

    public const HOSTINGER_REACH_SUBMISSIONS_META_KEY = '_hostinger_reach_submissions';
    public const HOSTINGER_REACH_IS_ACTIVE_META_KEY   = '_hostinger_reach_is_active';

    abstract public static function get_name(): string;
    abstract public function get_plugin_data( array $plugin_data ): array;

    public function __construct() {
        add_filter( 'hostinger_reach_plugin_data', array( $this, 'get_plugin_data' ) );
    }

    public function load_forms( array $forms, array $args ): array {
        if ( ! isset( $args['type'] ) || $args['type'] === $this->get_name() ) {
            $integration_forms = $this->get_forms( $args );

            return array_merge( $forms, $integration_forms );
        }

        return $forms;
    }

    public function on_form_activation_change( bool $repository_form_was_updated, string $form_id, bool $is_active ): bool {
        if ( $repository_form_was_updated ) {
            return $repository_form_was_updated;
        }

        $post = get_post( $form_id );
        if ( ! $post || $this->get_post_type() !== $post->post_type ) {
            return $repository_form_was_updated;
        }

        if ( $is_active && ! $this->is_form_valid( $post ) ) {
            throw new Exception( __( 'This form has not an email field. Create an email field in the form to allow it to be synced with Reach', 'hostinger-reach' ) );
        }

        return (bool) update_post_meta( (int) $form_id, Integration::HOSTINGER_REACH_IS_ACTIVE_META_KEY, $is_active ? 'yes' : 'no' );
    }

    public function update_form_submissions( int $id ): void {
        $submissions = (int) get_post_meta( $id, Integration::HOSTINGER_REACH_SUBMISSIONS_META_KEY, true );
        update_post_meta( $id, Integration::HOSTINGER_REACH_SUBMISSIONS_META_KEY, $submissions + 1 );
    }

    public function get_forms( array $args ): array {
        $posts = get_posts(
            array(
                'post_type' => $this->get_post_type(),
                'status'    => 'publish',
                'per_page'  => - 1,
            )
        );

        $forms = array_map(
            function ( $post ) {
                $form = new Form(
                    array(
                        'form_id'     => $post->ID,
                        'post_id'     => $post->ID,
                        'type'        => $this->get_name(),
                        'is_active'   => $this->is_form_valid( $post ) && $this->is_form_enabled( $post->ID ),
                        'submissions' => (int) get_post_meta( $post->ID, Integration::HOSTINGER_REACH_SUBMISSIONS_META_KEY, true ),
                    )
                );

                return $form->to_array();
            },
            $posts
        );

        return $forms;
    }

    public function is_form_enabled( int $form_id ): bool {
        $is_active_meta = get_post_meta( $form_id, Integration::HOSTINGER_REACH_IS_ACTIVE_META_KEY, true );

        if ( $is_active_meta === '' ) {
            return true;
        }

        return $is_active_meta === 'yes';
    }

    public function get_post_type(): string|null {
        return null;
    }

    public function is_form_valid( WP_Post $post ): bool {
        return true;
    }
}
