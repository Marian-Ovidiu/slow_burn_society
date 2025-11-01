<?php

namespace Hostinger\Reach\Admin\Notices;

use Hostinger\Reach\Admin\Menus;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Functions;

defined( 'ABSPATH' ) || exit;

class ConnectionNotice {

    private const NOTICE_NAME              = 'hostinger_reach_connection_notice';
    private const NOTICE_DISMISS_TRANSIENT = self::NOTICE_NAME . '_dismissed';
    private const NOTICE_ACTION            = self::NOTICE_NAME . '_action';


    private ReachApiHandler $reach_api_handler;
    private Functions $functions;

    public function __construct( ReachApiHandler $reach_api_handler, Functions $functions ) {
        $this->reach_api_handler = $reach_api_handler;
        $this->functions         = $functions;
    }

    public function init(): void {
        add_action( 'admin_notices', array( $this, 'display_notice' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_' . self::NOTICE_ACTION, array( $this, 'handle_ajax_action' ) );
    }

    public function display_notice(): void {
        if ( $this->should_render() ) {
            $this->render();
        }
    }

    public function enqueue_admin_assets(): void {
        if ( $this->should_render() ) {
            $this->enqueue_connection_notice_assets();
        }
    }

    public function handle_ajax_action(): void {
        check_ajax_referer( self::NOTICE_ACTION, 'nonce' );
        $choice = sanitize_text_field( $_POST['choice'] );

        switch ( $choice ) {
            case 'connect':
                $this->handle_connect();

                break;
            case 'dismiss':
                $this->handle_dismiss();
                break;
        }
    }

    private function handle_dismiss(): string {
        set_transient( self::NOTICE_DISMISS_TRANSIENT, true, WEEK_IN_SECONDS );

        return '';
    }

    private function handle_connect(): void {
        $response = $this->reach_api_handler->post_generate_auth_url();
        $data     = $response->get_data();
        $auth_url = $data['auth_url'] ?? '';

        if ( empty( $auth_url ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Could not generate auth URL', 'hostinger-reach' ),
                )
            );
        }

        wp_send_json_success(
            array(
                'redirect_url' => $auth_url,
            )
        );
    }

    private function render(): void {
        ?>
        <div id="hostinger-reach-connection-notice" class="notice notice-info is-dismissible hostinger-reach-notice">
            <div data-action="dismiss" class="hostinger-reach-action-button hostinger-reach-notice-close"></div>
            <div class="hostinger-reach-notice-wrap">
                <div class="hostinger-reach-notice-aside">
                    <img
                        alt="<?php esc_html_e( 'Hostinger Reach logo', 'hostinger-reach' ); ?>"
                        class="hostinger-reach-notice-logo"
                        src="<?php echo esc_url( $this->functions->get_icon_url( 'hostinger-logo' ) ); ?>"
                    />
                </div>
                <div class="hostinger-reach-notice-main">
                    <div class="hostinger-reach-notice-content">
                        <h3><?php esc_html_e( 'Connect Hostinger Reach to start Email Marketing', 'hostinger-reach' ); ?></h3>
                        <p><?php esc_html_e( 'Connect your Hostinger Reach account to start sending newsletters and managing subscribers. With Hostinger Reach, it\'s easy to grow your audience in just one click.', 'hostinger-reach' ); ?></p>
                    </div>
                    <div class="hostinger-reach-notice-actions">
                        <button data-action="connect" class="hostinger-reach-button hostinger-reach-action-button button button-primary">
                            <?php esc_html_e( 'Connect', 'hostinger-reach' ); ?>
                        </button>
                        <a href="<?php echo esc_url( Menus::get_reach_admin_url() ); ?>" class="button button-secondary hostinger-reach-button hostinger-reach-learn-more">
                            <?php esc_html_e( 'Learn more', 'hostinger-reach' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function should_render(): bool {
        $screen = get_current_screen();

        if ( ! $screen || ! in_array(
            $screen->id,
            array(
                'dashboard',
                'pages',
                'posts',
                'edit-page',
                'edit-post',
            ),
            true
        ) ) {
            return false;
        }

        if ( $this->is_dismissed() || $this->is_connected() ) {
            return false;
        }

        return true;
    }

    private function is_dismissed(): bool {
        return (bool) get_transient( self::NOTICE_DISMISS_TRANSIENT );
    }

    private function is_connected(): bool {
        return $this->reach_api_handler->is_connected();
    }

    private function enqueue_connection_notice_assets(): void {
        $asset_name = 'connection-notice';
        $js_file    = $this->functions->get_frontend_dir() . $asset_name . '.js';
        $css_file   = $this->functions->get_frontend_dir() . $asset_name . '.css';

        if ( $js_file ) {
            wp_enqueue_script(
                $asset_name,
                $this->functions->get_frontend_url() . $asset_name . '.js',
                array(),
                filemtime( $this->functions->get_frontend_dir() . $asset_name . '.js' ),
                true
            );

            wp_localize_script(
                $asset_name,
                self::NOTICE_NAME . '_data',
                array(
                    'action'  => self::NOTICE_ACTION,
                    'nonce'   => wp_create_nonce( self::NOTICE_ACTION ),
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                )
            );
        }

        if ( $css_file ) {
            wp_enqueue_style(
                $asset_name,
                $this->functions->get_frontend_url() . $asset_name . '.css',
                array(),
                filemtime( $this->functions->get_frontend_dir() . $asset_name . '.css' ),
            );
        }
    }
}
