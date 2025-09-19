<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Container;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class WpdbProvider implements ProviderInterface {
    public function register( Container $container ): void {
        global $wpdb;

        $container->set( wpdb::class, fn() => $wpdb );
    }
}
