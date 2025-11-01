<?php

namespace Hostinger\Reach\Integrations\WPFormsLite;

use Hostinger\Reach\Integrations\Integration;
use Hostinger\Reach\Integrations\IntegrationInterface;
use Hostinger\Reach\Dto\PluginData;
use WP_Post;

if ( ! DEFINED( 'ABSPATH' ) ) {
    exit;
}

class WpFormsLiteIntegration extends Integration implements IntegrationInterface {

    public const INTEGRATION_NAME = 'wpforms-lite';

    public function get_plugin_data(): PluginData {
        return PluginData::from_array(
            array(
                'id'           => self::INTEGRATION_NAME,
                'folder'       => 'wpforms-lite',
                'file'         => 'wpforms.php',
                'admin_url'    => 'admin.php?page=wpforms-overview',
                'add_form_url' => 'admin.php?page=wpforms-builder',
                'edit_url'     => 'admin.php?page=wpforms-builder&view=fields&form_id={form_id}',
                'url'          => 'https://wordpress.org/plugins/wpforms-lite/',
                'download_url' => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
                'title'        => __( 'WP Forms Lite', 'hostinger-reach' ),
            )
        );
    }

    public static function get_name(): string {
        return self::INTEGRATION_NAME;
    }

    public function get_post_type(): string {
        return 'wpforms';
    }

    public function active_integration_hooks(): void {
        add_action( 'wpforms_process_complete', array( $this, 'handle_submission' ), 10, 3 );
    }

    public function is_form_valid( WP_Post $post ): bool {
        $form_fields = wpforms_get_form_fields( $post->ID, array( 'email' ) );

        return ! empty( $form_fields );
    }

    public function handle_submission( array $fields, array $entry, array $form_data ): void {
        if ( ! $this->is_form_enabled( $form_data['id'] ) ) {
            return;
        }

        $email = $this->find_field( $fields, 'email' );
        if ( $email ) {
            do_action(
                'hostinger_reach_submit',
                array(
                    // translators: %s - form id.
                    'group'    => $form_data['settings']['form_title'] ?? sprintf( __( 'WP Forms Lite %s', 'hostinger-reach' ), $form_data['id'] ),
                    'email'    => $email,
                    'metadata' => array(
                        'plugin'  => self::INTEGRATION_NAME,
                        'form_id' => $form_data['id'],
                    ),
                )
            );
        }
    }

    private function find_field( array $fields, string $type ): string {
        foreach ( $fields as $field ) {
            if ( isset( $field['type'] ) && $field['type'] === $type ) {
                if ( isset( $field['value'] ) ) {
                    return $field['value'];
                }
            }
        }

        return '';
    }
}
