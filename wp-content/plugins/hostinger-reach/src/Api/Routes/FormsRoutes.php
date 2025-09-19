<?php

namespace Hostinger\Reach\Api\Routes;

use Hostinger\Reach\Api\ApiKeyManager;
use Hostinger\Reach\Api\Handlers\FormsApiHandler;
use Hostinger\Reach\Providers\IntegrationsProvider;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class FormsRoutes extends Routes {
    private FormsApiHandler $handler;
    private ApiKeyManager $api_key_manager;

    public function __construct( FormsApiHandler $handler, ApiKeyManager $api_key_manager ) {
        $this->handler         = $handler;
        $this->api_key_manager = $api_key_manager;
    }

    public function init(): void {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        add_action( 'rest_api_init', array( $this, 'add_block_check_field_to_pages' ) );
    }

    public function register_routes(): void {
        register_rest_route(
            HOSTINGER_REACH_PLUGIN_REST_API_BASE,
            'contact-lists',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->handler, 'get_contact_lists_handler' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        register_rest_route(
            HOSTINGER_REACH_PLUGIN_REST_API_BASE,
            'forms',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->handler, 'get_forms_handler' ),
                'permission_callback' => array( $this, 'permission_check' ),
                'args'                => array(
                    'contact_list_id' => array(
                        'required'          => false,
                        'type'              => 'int',
                        'validate_callback' => function ( $param ) {
                            return is_numeric( $param );
                        },
                        'sanitize_callback' => function ( $param ) {
                            return absint( $param );
                        },
                    ),
                    'type'            => array(
                        'required'          => false,
                        'type'              => 'string',
                        'validate_callback' => function ( $param ) {
                            return array_key_exists( $param, IntegrationsProvider::INTEGRATIONS );
                        },
                    ),
                ),
            )
        );

        register_rest_route(
            HOSTINGER_REACH_PLUGIN_REST_API_BASE,
            'forms',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this->handler, 'post_forms_handler' ),
                'permission_callback' => array( $this, 'permission_check' ),
                'args'                => array(
                    'form_id'   => array(
                        'required' => true,
                        'type'     => 'string',
                    ),
                    'is_active' => array(
                        'required'          => false,
                        'type'              => 'boolean',
                        'sanitize_callback' => function ( $param ) {
                            return (int) $param;
                        },
                    ),
                ),
            )
        );
    }

    public function add_block_check_field_to_pages(): void {
        register_rest_field(
            'page',
            '_hostinger_reach_plugin_has_subscription_block',
            array(
                'get_callback' => array( $this, 'has_subscription_block' ),
                'schema'       => array(
                    'type' => 'boolean',
                ),
            )
        );
        register_rest_field(
            'page',
            '_hostinger_reach_plugin_is_elementor',
            array(
                'get_callback' => array( $this, 'is_elementor' ),
                'schema'       => array(
                    'type' => 'boolean',
                ),
            )
        );
    }

    public function is_elementor( array $post, string $field_name, WP_REST_Request $request ): bool {
        if ( ! $request->has_param( 'hostinger_reach_page_query' ) ) {
            return false;
        }

        return $this->handler->get_functions()->is_elementor( $post['id'] );
    }

    public function has_subscription_block( array $post, string $field_name, WP_REST_Request $request ): bool {
        if ( ! $request->has_param( 'hostinger_reach_page_query' ) ) {
            return false;
        }

        return $this->handler->get_functions()->has_reach_form( $post['id'] );
    }
}
