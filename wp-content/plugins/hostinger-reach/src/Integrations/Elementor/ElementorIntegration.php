<?php

namespace Hostinger\Reach\Integrations\Elementor;

use Elementor\Core\Documents_Manager;
use Elementor\Plugin as ElementorPlugin;
use Elementor\Widget_Base;
use Hostinger\Reach\Integrations\IntegrationInterface;
use Hostinger\Reach\Integrations\IntegrationWithForms;
use Exception;
use Hostinger\Reach\Dto\PluginData;
use WP_Post;

if ( ! DEFINED( 'ABSPATH' ) ) {
    exit;
}

class ElementorIntegration extends IntegrationWithForms implements IntegrationInterface {

    public const INTEGRATION_NAME = 'elementor';
    protected SubscriptionFormElementorWidget $widget;

    public function init(): void {
        parent::init();
        add_action( 'hostinger_reach_integration_activated', array( $this, 'set_elementor_onboarding' ) );
    }

    public function active_integration_hooks(): void {
        add_action( 'elementor/widgets/register', array( $this, 'register_new_widgets' ) );
        add_action( 'transition_post_status', array( $this, 'handle_transition_post_status' ), 10, 3 );
        add_filter( 'hostinger_reach_get_group', array( $this, 'filter_hostinger_reach_get_group' ), 10, 2 );
    }

    public function set_elementor_onboarding( string $integration_name ): void {
        if ( $integration_name === self::INTEGRATION_NAME ) {
            update_option( 'elementor_onboarded', 1 );
        }
    }

    public function handle_transition_post_status( string $new_status, string $old_status, WP_Post $post ): void {
        if ( $new_status === 'publish' ) {
            $this->set_forms( $post );
            $this->maybe_unset_forms( $post );
        } elseif ( $old_status === 'publish' ) {
            $this->unset_all_forms( $post );
        }
    }

    public function register_new_widgets(): void {
        ElementorPlugin::instance()->widgets_manager->register( $this->get_widget() );
    }

    public function filter_hostinger_reach_get_group( string $group, string $form_id ): string {
        if ( ! empty( $group ) || ! $this->is_elementor_form_id( $form_id ) ) {
            return $group;
        }

        try {
            $form = $this->form_repository->get( $form_id );
            $post = $form['post'] ?? null;
            if ( $post ) {
                return $post['post_title'] ?? self::INTEGRATION_NAME;
            }
        } catch ( Exception $e ) {
            return self::INTEGRATION_NAME;
        }

        return self::INTEGRATION_NAME;
    }

    public static function get_name(): string {
        return self::INTEGRATION_NAME;
    }

    public function get_form_ids( WP_Post $post ): array {
        return $this->get_elementor_form_ids_from_content( $post->post_content );
    }

    public function get_plugin_data(): PluginData {
        if ( class_exists( 'Elementor\Core\Documents_Manager' ) ) {
            $add_form_url = Documents_Manager::get_create_new_post_url();
        } else {
            $add_form_url = '';
        }

        return new PluginData(
            self::INTEGRATION_NAME,
            __( 'Elementor', 'hostinger-reach' ),
            'elementor',
            'elementor.php',
            'admin.php?page=elementor',
            $add_form_url,
            'post.php?post={post_id}&action=elementor',
            'https://wordpress.org/plugins/elementor/',
            'https://downloads.wordpress.org/plugin/elementor.zip',
            null,
            false,
            false,
            false,
        );
    }

    private function get_widget(): Widget_Base {
        return new SubscriptionFormElementorWidget();
    }

    private function set_forms( WP_Post $post ): void {
        $form_ids = $this->get_elementor_form_ids_from_content( $post->post_content );
        foreach ( $form_ids as $form_id ) {
            $form = array(
                'form_id' => $form_id,
                'type'    => self::INTEGRATION_NAME,
            );

            if ( $this->form_repository->exists( $form_id ) ) {
                $this->form_repository->update( $form );
            } else {
                $this->form_repository->insert( array_merge( $form, array( 'post_id' => $post->ID ) ) );
            }
        }
    }

    private function get_elementor_form_ids_from_content( string $content ): array {
        $form_ids = array();
        $pattern  = '/<form id="' . SubscriptionFormElementorWidget::FORM_ID_PREFIX . '(\d+)"/';
        preg_match_all( $pattern, $content, $matches );
        foreach ( $matches[1] as $form_id ) {
            $form_ids[] = SubscriptionFormElementorWidget::FORM_ID_PREFIX . $form_id;
        }

        return $form_ids;
    }

    private function is_elementor_form_id( string $form_id ): bool {
        return str_starts_with( $form_id, SubscriptionFormElementorWidget::FORM_ID_PREFIX );
    }
}
