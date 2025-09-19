<?php

namespace Hostinger\Reach\Integrations\WooCommerce;

use Automattic\WooCommerce\Internal\Admin\Onboarding\OnboardingProfile;
use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Integrations\IntegrationInterface;
use Hostinger\Reach\Integrations\IntegrationWithForms;
use Hostinger\Reach\Repositories\FormRepository;
use WC_Order;
use WP_Post;
use WP_REST_Request;

class WooCommerceIntegration extends IntegrationWithForms implements IntegrationInterface {
    public FormRepository $form_repository;
    protected IntegrationsApiHandler $integrations_api_handler;
    protected ReachApiHandler $reach_api_handler;

    public const INTEGRATION_NAME     = 'woocommerce';
    public const OPTIN_KEY            = 'hostinger_reach_optin';
    public const ORDER_META_OPTIN_KEY = '_wc_other/hostinger-reach/newsletter-optin';

    public function __construct( FormRepository $form_repository, IntegrationsApiHandler $integrations_api_handler, ReachApiHandler $reach_api_handler ) {
        parent::__construct( $form_repository );
        $this->reach_api_handler        = $reach_api_handler;
        $this->integrations_api_handler = $integrations_api_handler;
    }

    public static function get_name(): string {
        return self::INTEGRATION_NAME;
    }

    public function init(): void {
        parent::init();
        add_action( 'hostinger_reach_integration_activated', array( $this, 'set_woocommerce_onboarding_skipped' ) );
        if ( $this->integrations_api_handler->is_active( self::INTEGRATION_NAME ) ) {
            $this->add_form();
        }

        if ( $this->form_repository->is_form_active( self::INTEGRATION_NAME ) ) {
            $this->init_optin_actions();

            add_action( 'woocommerce_thankyou', array( $this, 'subscribe_customer_to_hostinger_reach' ) );
            add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_optin' ) );
            add_action( 'hostinger_reach_contact_submitted', array( $this, 'handle_submission' ) );
        }
    }

    public function init_optin_actions(): void {
        if ( did_action( 'woocommerce_blocks_loaded' ) ) {
            $this->register_checkout_blocks_field();
        } else {
            add_action( 'woocommerce_blocks_loaded', array( $this, 'register_checkout_blocks_field' ) );
        }

        add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'handle_checkout_blocks_optin' ), 10, 2 );
        add_action( 'woocommerce_checkout_after_terms_and_conditions', array( $this, 'add_optin_checkbox' ) );
    }


    public function register_checkout_blocks_field(): void {
        if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
            return;
        }

        woocommerce_register_additional_checkout_field(
            array(
                'id'                         => 'hostinger-reach/newsletter-optin',
                'label'                      => __( 'Subscribe to our newsletter', 'hostinger-reach' ),
                'location'                   => 'contact',
                'type'                       => 'checkbox',
                'required'                   => false,
                'attributes'                 => array(
                    'data-custom' => 'hostinger-reach-optin',
                ),
                'show_in_order_confirmation' => true,
            )
        );
    }

    public function add_optin_checkbox(): void {
        $this->load_template();
    }

    public function handle_optin( int $oder_id ): void {
        $this->set_opted_in( isset( $_POST[ self::OPTIN_KEY ] ), $oder_id );
    }

    public function handle_checkout_blocks_optin( WC_Order $order, WP_REST_Request $request ): void {
        $newsletter_optin = false;

        if ( ! $newsletter_optin && isset( $request['additional_fields']['hostinger-reach/newsletter-optin'] ) ) {
            $newsletter_optin = (bool) $request['additional_fields']['hostinger-reach/newsletter-optin'];
        }

        $this->set_opted_in( $newsletter_optin, $order->get_id() );
    }

    public function add_form(): void {
        $checkout_page_id = wc_get_page_id( 'checkout' );

        if ( $checkout_page_id && ! $this->form_repository->exists( self::INTEGRATION_NAME ) ) {
            $this->form_repository->insert(
                array(
                    'form_id' => self::INTEGRATION_NAME,
                    'type'    => self::INTEGRATION_NAME,
                    'post_id' => $checkout_page_id,
                )
            );
        }
    }

    public function set_woocommerce_onboarding_skipped( string $integration_name ): void {
        if ( $integration_name === self::INTEGRATION_NAME && class_exists( 'Automattic\WooCommerce\Internal\Admin\Onboarding\OnboardingProfile' ) ) {
            update_option( OnboardingProfile::DATA_OPTION, array( 'skipped' => true ) );
        }
    }

    public function handle_submission( array $data ): void {
        $data['form_id'] = self::INTEGRATION_NAME;
        $this->form_repository->submit( $data );
    }

    public function subscribe_customer_to_hostinger_reach( int $order_id ): void {
        if ( ! $this->is_opted_in( $order_id ) ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $email   = $order->get_billing_email();
        $name    = $order->get_billing_first_name();
        $surname = $order->get_billing_last_name();
        if ( $email ) {
            $this->reach_api_handler->post_contact(
                array(
                    'group'    => self::INTEGRATION_NAME,
                    'email'    => $email,
                    'name'     => $name,
                    'surname'  => $surname,
                    'metadata' => array(
                        'plugin' => self::INTEGRATION_NAME,
                    ),
                )
            );
        }
    }

    public function get_plugin_data( array $plugin_data ): array {
        $plugin_data[ self::INTEGRATION_NAME ] = array(
            'folder'       => 'woocommerce',
            'file'         => 'woocommerce.php',
            'admin_url'    => 'admin.php?page=wc-admin',
            'add_form_url' => null,
            'edit_url'     => null,
            'url'          => 'https://wordpress.org/plugins/woocommerce/',
            'download_url' => 'https://downloads.wordpress.org/plugin/woocommerce.zip',
            'title'        => __( 'WooCommerce', 'hostinger-reach' ),
        );

        return $plugin_data;
    }

    public function get_form_ids( WP_Post $post ): array {
        return array();
    }

    private function set_opted_in( bool $opted_in, mixed $oder_id ): void {
        $customer_id = get_current_user_id();
        $order       = wc_get_order( $oder_id );

        if ( $customer_id > 0 ) {
            update_user_meta( $customer_id, self::OPTIN_KEY, $opted_in ? 'yes' : 'no' );
        } elseif ( $order ) {
            $order->update_meta_data( self::ORDER_META_OPTIN_KEY, $opted_in ? 'yes' : 'no' );
            $order->save();
        }
    }

    private function is_opted_in( mixed $oder_id = false ): bool {
        $customer_id = get_current_user_id();
        $order       = wc_get_order( $oder_id );
        $is_opted_in = false;

        if ( $customer_id > 0 ) {
            $is_opted_in = get_user_meta( get_current_user_id(), self::OPTIN_KEY, true );
        } elseif ( $order ) {
            $is_opted_in = $order->get_meta( self::ORDER_META_OPTIN_KEY, true );
        }

        return $is_opted_in === 'yes';
    }

    private function load_template(): void {
        $template      = 'optin-checkbox.php';
        $template_path = 'hostinger-reach/';
        $default_path  = HOSTINGER_REACH_PLUGIN_DIR . 'templates/';

        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Extracting variables for use in template.
        extract( array( 'is_opted_in' => $this->is_opted_in() ) );

        $located = wc_locate_template( $template, $template_path, $default_path );
        if ( file_exists( $located ) ) {
            include $located;
        }
    }
}
