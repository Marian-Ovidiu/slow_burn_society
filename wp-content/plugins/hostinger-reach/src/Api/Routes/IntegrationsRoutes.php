<?php

namespace Hostinger\Reach\Api\Routes;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Providers\IntegrationsProvider;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class IntegrationsRoutes extends Routes {

    private IntegrationsApiHandler $handler;

    public function __construct( IntegrationsApiHandler $handler ) {
        $this->handler = $handler;
    }

    public function register_routes(): void {
        register_rest_route(
            HOSTINGER_REACH_PLUGIN_REST_API_BASE,
            'integrations',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->handler, 'get_integrations_handler' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        register_rest_route(
            HOSTINGER_REACH_PLUGIN_REST_API_BASE,
            'integrations',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this->handler, 'post_integrations_handler' ),
                'permission_callback' => array( $this, 'permission_check' ),
                'args'                => array(
                    'integration' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'validate_callback' => function ( $param ) {
                            return array_key_exists( $param, IntegrationsProvider::INTEGRATIONS );
                        },
                    ),
                    'is_active'   => array(
                        'required' => true,
                        'type'     => 'boolean',
                    ),
                ),
            )
        );
    }
}
