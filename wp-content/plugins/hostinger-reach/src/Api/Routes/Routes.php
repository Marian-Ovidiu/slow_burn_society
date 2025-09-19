<?php

namespace Hostinger\Reach\Api\Routes;

use Hostinger\Reach\Api\ApiKeyManager;
use Hostinger\Reach\Api\Handlers\ApiHandler;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Routes {
    private ApiHandler $handler;
    private ApiKeyManager $api_key_manager;

    public function __construct( ApiHandler $handler, ApiKeyManager $api_key_manager ) {
        $this->handler         = $handler;
        $this->api_key_manager = $api_key_manager;
    }

    public function init(): void {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function permission_check(): bool {
        if ( has_action( 'litespeed_control_set_nocache' ) ) {
            do_action(
                'litespeed_control_set_nocache',
                'Custom Rest API endpoint, not cacheable.'
            );
        }

        if ( empty( is_user_logged_in() ) ) {
            return false;
        }

        return current_user_can( 'manage_options' );
    }
}
