<?php
/**
 * Plugin Name:       Hostinger Reach
 * Plugin URI:        https://hostinger.com
 * Description:       Integrate your WordPress site with Hostinger Reach.
 * Version:           1.0.9
 * Author:            Hostinger
 * Requires PHP:      8.0
 * Requires at least: 6.0
 * Tested up to:      6.8
 * Author URI:        https://www.hostinger.com/email-marketing
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hostinger-reach
 * Domain Path:       /languages
 *
 * @package HostingerReach
 */

use Hostinger\Reach\Setup\Activator;
use Hostinger\WpMenuManager\Manager;
use Hostinger\Surveys\Loader;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

define( 'HOSTINGER_REACH_PLUGIN_VERSION', '1.0.9' );
define( 'HOSTINGER_REACH_DB_VERSION', '1.0.0' );
define( 'HOSTINGER_REACH_MINIMUM_PHP_VERSION', '8.0' );
define( 'HOSTINGER_REACH_PLUGIN_FILE', __FILE__ );
define( 'HOSTINGER_REACH_PLUGIN_SLUG', basename( __FILE__, '.php' ) );
define( 'HOSTINGER_REACH_PLUGIN_URL', plugin_dir_url( HOSTINGER_REACH_PLUGIN_FILE ) );
define( 'HOSTINGER_REACH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HOSTINGER_REACH_PLUGIN_REST_API_BASE', 'hostinger-reach/v1' );
define( 'HOSTINGER_REACH_REST_URI', 'https://reach.hostinger.com' );
define( 'HOSTINGER_REACH_DEFAULT_CONTACT_LIST', 'WordPress' );

$hostinger_dir_parts        = explode( '/', __DIR__ );
$hostinger_server_root_path = '/' . $hostinger_dir_parts[1] . '/' . $hostinger_dir_parts[2];
define( 'HOSTINGER_REACH_WP_TOKEN', $hostinger_server_root_path . '/.api_token' );

if ( ! version_compare( phpversion(), HOSTINGER_REACH_MINIMUM_PHP_VERSION, '>=' ) ) {
    add_action(
        'admin_notices',
        function (): void {
            ?>
            <div class="notice notice-error is-dismissible hts-theme-settings">
                <p>
                    <strong><?php echo esc_html( __( 'Attention:', 'hostinger-reach' ) ); ?></strong>
                    <?php /* translators: %s: PHP version */ ?>
                    <?php echo esc_html( sprintf( __( 'Hostinger Reach requires minimum PHP version of <b>%s</b>. ', 'hostinger-reach' ), HOSTINGER_REACH_MINIMUM_PHP_VERSION ) ); ?>
                </p>
                <p>
                    <?php /* translators: %s: PHP version */ ?>
                    <?php echo esc_html( sprintf( __( 'You are running <b>%s</b> PHP version.', 'hostinger-reach' ), phpversion() ) ); ?>
                </p>
            </div>
            <?php
        }
    );

    return;
}

$vendor_file       = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload_packages.php';
$composer_autoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ( file_exists( $vendor_file ) ) {
    require_once $vendor_file;
}

if ( file_exists( $composer_autoload ) ) {
    require_once $composer_autoload;
}

if ( class_exists( 'Hostinger\Reach\Setup\Activator' ) ) {
    new Activator( __FILE__ );
}

if ( ! function_exists( 'hostinger_load_menus' ) ) {
    function hostinger_load_menus(): void {
        $manager = Manager::getInstance();
        $manager->boot();
    }
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
    add_action( 'plugins_loaded', 'hostinger_load_menus' );
}

if ( ! function_exists( 'hostinger_add_surveys' ) ) {
    function hostinger_add_surveys(): void {
        $surveys = Loader::getInstance();
        $surveys->boot();
    }
}

if ( ! empty( $_SERVER['H_PLATFORM'] ) && ! has_action( 'plugins_loaded', 'hostinger_add_surveys' ) ) {
    add_action( 'plugins_loaded', 'hostinger_add_surveys' );
}
