<?php

namespace Hostinger\Reach\Integrations;

use Hostinger\Reach\Models\Form;
use Exception;
use Hostinger\Reach\Dto\PluginData;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

abstract class Integration {

    public const HOSTINGER_REACH_SUBMISSIONS_META_KEY = '_hostinger_reach_submissions';
    public const HOSTINGER_REACH_IS_ACTIVE_META_KEY   = '_hostinger_reach_is_active';

    /**
     * Unique name for your integration
     */
    abstract public static function get_name(): string;

    /**
     * Your integration data.
     *
     * @return PluginData Plugin data model.
     *
     */
    abstract public function get_plugin_data(): PluginData;

    /**
     * Hooks to run ONLY when the integration is active.
     * i.e Form submission should be here.
     */
    abstract public function active_integration_hooks(): void;

    /**
     * Returns the post-type of the form.
     * Override and implement this if your integration is based on post types.
     * Otherwise, override get_forms() method.
     *
     */
    public function get_post_type(): string|null {
        return null;
    }

    /**
     * Method to return the forms of the integration.
     *
     * If your integration is based on post-types, DONT override this method. Override get_post_type() instead.
     * @see Hostinger\Reach\Models\Form
     *
     * @return array An array of forms based on Hostinger\Reach\Models\Form
     *
     * 'form_id'     => The Form Unique ID
     * 'post_id'     => (Optional) associated Post ID
     * 'type'        => $this->get_name(), (Your integration name)
     * 'is_active'   => True or false indicating the state of the form
     * 'submissions' =>Number indicating the submission counter
     */
    public function get_forms(): array {
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

    /**
     * Logic to update the submission counter when a form is submitted.
     * Override this if your Integration is not post-type based integration.
     *
     * @param array $data Submission data
     */
    public function update_form_submissions( array $data ): void {
        if ( ! isset( $data['metadata']['form_id'] ) ) {
            return;
        }

        $id          = $data['metadata']['form_id'];
        $submissions = (int) get_post_meta( $id, Integration::HOSTINGER_REACH_SUBMISSIONS_META_KEY, true );
        update_post_meta( $id, Integration::HOSTINGER_REACH_SUBMISSIONS_META_KEY, $submissions + 1 );
    }


    public function init(): void {
        add_filter( 'hostinger_reach_integrations', array( $this, 'load_integration' ) );
        add_filter( 'hostinger_reach_plugin_data', array( $this, 'load_plugin_data' ) );
        add_action( 'hostinger_reach_after_form_state_is_set', array( $this, 'on_form_activation_change' ), 10, 3 );
        add_action( 'hostinger_reach_integrations_loaded', array( $this, 'init_active_integration' ) );
        add_action( 'hostinger_reach_contact_submitted', array( $this, 'on_contact_form_submission' ) );
    }

    public function load_plugin_data( array $plugin_data ): array {
        $plugin_data[ $this->get_name() ] = $this->get_plugin_data();

        return $plugin_data;
    }

    public function load_integration( array $integrations ): array {
        $integrations[ $this->get_name() ] = $this::class;

        return $integrations;
    }

    public function init_active_integration( array $integrations ): void {
        $integration_is_active = $integrations[ $this->get_name() ]['is_active'] ?? false;

        if ( $integration_is_active ) {
            add_filter( 'hostinger_reach_forms', array( $this, 'load_forms' ), 10, 2 );
            $this->active_integration_hooks();
        }
    }

    public function load_forms( array $forms, array $args ): array {
        if ( ! isset( $args['type'] ) || $args['type'] === $this->get_name() ) {
            $integration_forms = $this->get_forms();

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

    public function on_contact_form_submission( array $data ): void {
        if ( ! isset( $data['metadata']['plugin'] ) || $data['metadata']['plugin'] !== $this->get_name() ) {
            return;
        }

        $this->update_form_submissions( $data );
    }

    public function is_form_enabled( int $form_id ): bool {
        $is_active_meta = get_post_meta( $form_id, Integration::HOSTINGER_REACH_IS_ACTIVE_META_KEY, true );

        if ( $is_active_meta === '' ) {
            return true;
        }

        return $is_active_meta === 'yes';
    }

    public function is_form_valid( WP_Post $post ): bool {
        return true;
    }
}
