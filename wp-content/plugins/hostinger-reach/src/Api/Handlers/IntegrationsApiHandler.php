<?php

namespace Hostinger\Reach\Api\Handlers;

use Hostinger\Reach\Functions;
use Hostinger\Reach\Integrations\PluginManager;
use Hostinger\Reach\Integrations\ReachFormIntegration;
use Hostinger\Reach\Providers\IntegrationsProvider;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class IntegrationsApiHandler extends ApiHandler {
    public const INTEGRATIONS_OPTION_NAME = 'hostinger_reach_integrations';

    public PluginManager $plugin_manager;

    public function __construct( Functions $functions, PluginManager $plugin_manager ) {
        parent::__construct( $functions );
        $this->plugin_manager = $plugin_manager;
    }

    public function is_active( string $integration_name ): bool {
        $data = $this->get_integrations_data();

        return $data[ $integration_name ]['is_active'] ?? false;
    }

    public function get_integrations_handler(): WP_REST_Response {
        return $this->handle_response(
            array(
                'response' => array(
                    'code' => 200,
                ),
                'body'     => wp_json_encode( $this->get_integrations_data() ),
            )
        );
    }

    public function post_integrations_handler( WP_REST_Request $request ): WP_REST_Response {
        $is_active     = $request->get_param( 'is_active' );
        $integration   = $request->get_param( 'integration' );
        $should_update = ! $is_active || $this->activate_integration( $integration );

        $integration_data = $this->get_integration_data( $integration );
        if ( $should_update && isset( $integration_data['is_active'] ) && $integration_data['is_active'] === $is_active ) {
            $should_update = false;
        }

        if ( $should_update ) {
            $this->save_integration( $integration, array( 'is_active' => $is_active ) );
        }

        return $this->handle_response(
            array(
                'response' => array(
                    'code' => 200,
                ),
            )
        );
    }

    public function activate_integration( string $integration_name ): bool {
        $integration_class = IntegrationsProvider::INTEGRATIONS[ $integration_name ];
        if ( ! isset( $integration_class ) ) {
            return false;
        }

        $installed = $this->plugin_manager->install( $integration_name );
        if ( is_wp_error( $installed ) ) {
            return false;
        }

        $activated = $this->plugin_manager->activate( $integration_name );
        if ( $activated ) {
            do_action( 'hostinger_reach_integration_activated', $integration_name );
        }

        return $activated;
    }

    public function get_integration_data( string $integration_name ): array {
        $integrations_data = get_option( self::INTEGRATIONS_OPTION_NAME, array() );

        return $integrations_data[ $integration_name ] ?? array();
    }

    public function get_integrations_data(): array {
        $available_integrations       = IntegrationsProvider::INTEGRATIONS;
        $integrations_state           = get_option( self::INTEGRATIONS_OPTION_NAME, array() );
        $available_integrations_state = array_intersect_key( $integrations_state, $available_integrations );
        $integrations                 = array();

        foreach ( $available_integrations as $integration_name => $integration_class ) {

            $is_hostinger_reach = $integration_name === ReachFormIntegration::INTEGRATION_NAME;

            $plugin_data = $this->plugin_manager->get_plugin( $integration_name );
            $is_active   = $this->plugin_manager->is_active( $integration_name ) && ( $available_integrations_state[ $integration_name ]['is_active'] ?? false );

            $integrations[ $integration_name ] = array(
                'is_plugin_active' => $is_hostinger_reach || $this->plugin_manager->is_active( $integration_name ),
                'is_active'        => $is_hostinger_reach || $is_active,
                'title'            => $plugin_data['title'] ?? '',
                'url'              => $plugin_data['url'] ?? '',
                'admin_url'        => $plugin_data['admin_url'] ?? '',
                'edit_url'         => $plugin_data['edit_url'] ?? '',
                'add_form_url'     => $plugin_data['add_form_url'] ?? '',
            );
        }

        return $integrations;
    }

    private function save_integration( string $integration_name, array $data ): bool {
        $integrations_data                      = $this->get_integrations_data();
        $integrations_data[ $integration_name ] = $data;

        return $this->save_integrations_data( $integrations_data );
    }


    private function save_integrations_data( array $data ): bool {
        return update_option( self::INTEGRATIONS_OPTION_NAME, $data );
    }
}
