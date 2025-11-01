<?php

namespace Hostinger\Reach\Integrations;

use Hostinger\Reach\Dto\PluginData;
use Plugin_Upgrader;
use Plugin_Upgrader_Skin;
use WP_Error;

if ( ! DEFINED( 'ABSPATH' ) ) {
    die;
}

class PluginManager {

    public static function plugin_data(): array {
        return apply_filters( 'hostinger_reach_plugin_data', array() );
    }

    public function install( string $plugin_name ): bool|WP_Error {
        if ( $this->is_installed( $plugin_name ) ) {
            return true;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';

        $plugin = $this->get_plugin( $plugin_name );
        if ( is_null( $plugin ) ) {
            return new WP_Error( 'plugin_not_found', 'Plugin not found' );
        }

        $plugin_data = $plugin->to_array();
        $temp_file   = download_url( $plugin_data['download_url'] );

        if ( is_wp_error( $temp_file ) ) {
            return $temp_file;
        }

        $upgrader = new Plugin_Upgrader( new Plugin_Upgrader_Skin() );
        $result   = $upgrader->install( $temp_file );

        wp_delete_file( $temp_file );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return true;
    }

    public function activate( string $plugin_name ): bool {
        if ( ! $this->is_installed( $plugin_name ) ) {
            return false;
        }

        if ( $this->is_active( $plugin_name ) ) {
            return true;
        }

        return is_null( activate_plugin( $this->get_plugin_path( $plugin_name ) ) );
    }

    public function is_installed( string $plugin_name ): bool {
        return file_exists( WP_PLUGIN_DIR . '/' . $this->get_plugin_path( $plugin_name ) );
    }

    public function is_active( string $plugin_name ): bool {
        $plugin_path = $this->get_plugin_path( $plugin_name );

        return is_plugin_active( $plugin_path );
    }

    public function get_plugin_path( string $plugin_name ): string {
        $plugin = $this->get_plugin( $plugin_name );
        if ( is_null( $plugin ) ) {
            return '/';
        }

        $plugin_data = $plugin->to_array();

        if ( ! isset( $plugin_data['folder'] ) || ! isset( $plugin_data['file'] ) ) {
            return '/';
        }

        return $plugin_data['folder'] . '/' . $plugin_data['file'];
    }

    public function get_plugin( string $plugin_name ): ?PluginData {
        $plugin_data = self::plugin_data();

        return $plugin_data[ $plugin_name ] ?? null;
    }
}
