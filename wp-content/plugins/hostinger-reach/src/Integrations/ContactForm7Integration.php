<?php

namespace Hostinger\Reach\Integrations;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use WPCF7_ContactForm;
use WPCF7_Submission;
use WP_Post;
use WPCF7_FormTag;

if ( ! DEFINED( 'ABSPATH' ) ) {
    exit;
}

class ContactForm7Integration extends Integration implements IntegrationInterface {

    public const INTEGRATION_NAME = 'contact-form-7';
    protected ReachApiHandler $reach_api_handler;
    protected IntegrationsApiHandler $integrations_api_handler;

    public function __construct( ReachApiHandler $reach_api_handler, IntegrationsApiHandler $integrations_api_handler ) {
        parent::__construct();
        $this->integrations_api_handler = $integrations_api_handler;
        $this->reach_api_handler        = $reach_api_handler;
    }

    public function init(): void {
        if ( $this->integrations_api_handler->is_active( self::INTEGRATION_NAME ) ) {
            add_action( 'wpcf7_mail_sent', array( $this, 'handle_submission' ), 10, 1 );
            add_filter( 'hostinger_reach_forms', array( $this, 'load_forms' ), 10, 2 );
            add_filter( 'hostinger_reach_after_form_state_is_set', array( $this, 'on_form_activation_change' ), 10, 3 );
        }
    }

    public function handle_submission( WPCF7_ContactForm $contact_form ): void {
        if ( ! $this->is_form_enabled( $contact_form->id() ) ) {
            return;
        }

        $contact_list = $contact_form->title();
        $email        = $this->get_field_data( $contact_form, array( 'basetype' => 'email' ) );
        if ( $email ) {
            $response = $this->reach_api_handler->post_contact(
                array(
                    'group'    => $contact_list,
                    'email'    => $email,
                    'metadata' => array(
                        'plugin' => self::INTEGRATION_NAME,
                    ),
                )
            );

            if ( $response->get_status() < 300 ) {
                $this->update_form_submissions( $contact_form->id() );
            }
        }
    }

    public function get_field_data( WPCF7_ContactForm $contact_form, array $condition ): string {
        $tag = $this->find_field( $contact_form, $condition );

        if ( ! is_null( $tag ) ) {
            $submission = WPCF7_Submission::get_instance();
            return $submission->get_posted_data( $tag->name );
        }

        return '';
    }

    public function find_field( WPCF7_ContactForm $contact_form, array $condition ): ?WPCF7_FormTag {
        $tags = $contact_form->scan_form_tags( $condition );

        if ( ! empty( $tags ) ) {
            return $tags[0];
        }

        return null;
    }

    public function get_post_type(): string {
        return 'wpcf7_contact_form';
    }

    public static function get_name(): string {
        return self::INTEGRATION_NAME;
    }


    public function is_form_valid( WP_Post $post ): bool {
        $contact_form = WPCF7_ContactForm::get_instance( $post->ID );
        if ( is_null( $contact_form ) ) {
            return false;
        }

        return ! is_null( $this->find_field( $contact_form, array( 'basetype' => 'email' ) ) );
    }

    public function get_plugin_data( array $plugin_data ): array {
        $plugin_data[ self::INTEGRATION_NAME ] = array(
            'folder'       => 'contact-form-7',
            'file'         => 'wp-contact-form-7.php',
            'admin_url'    => 'admin.php?page=wpcf7',
            'add_form_url' => 'admin.php?page=wpcf7-new',
            'edit_url'     => 'admin.php?page=wpcf7&post={form_id}&action=edit',
            'url'          => 'https://wordpress.org/plugins/contact-form-7/',
            'download_url' => 'https://downloads.wordpress.org/plugin/contact-form-7.zip',
            'title'        => __( 'Contact Form 7', 'hostinger-reach' ),
        );

        return $plugin_data;
    }
}
