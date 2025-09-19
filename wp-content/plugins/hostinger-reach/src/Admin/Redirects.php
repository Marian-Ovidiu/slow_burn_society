<?php

namespace Hostinger\Reach\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Redirects {
    private string $platform;

    public function init(): void {
        if ( ! isset( $_GET['platform'] ) ) {
            return;
        }

        $this->platform = sanitize_text_field( $_GET['platform'] );
        $this->login_redirect();
    }

    public function login_redirect(): void {
        if ( $this->platform === HOSTINGER_REACH_PLUGIN_SLUG ) {
            add_action(
                'init',
                static function (): void {
                    $redirect_url = admin_url( 'admin.php?page=hostinger-reach' );
                    wp_safe_redirect( $redirect_url );
                    exit;
                }
            );
        }
    }
}
