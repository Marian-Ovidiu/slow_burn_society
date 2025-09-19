<?php

namespace Hostinger\Reach\Setup;

use Hostinger\Reach\Boot;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Activator {
    public function __construct( string $plugin_file_name ) {
        register_activation_hook( $plugin_file_name, array( $this, 'activate_plugin' ) );
        add_action( 'plugins_loaded', array( $this, 'boot' ) );
    }

    public function activate_plugin(): void {
        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }
    }

    public function boot(): void {
        $boot = Boot::get_instance();
        $boot->plugins_loaded();
    }
}
