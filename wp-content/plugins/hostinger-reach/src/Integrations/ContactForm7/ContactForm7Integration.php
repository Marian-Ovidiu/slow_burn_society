<?php

namespace Hostinger\Reach\Integrations\ContactForm7;

use Hostinger\Reach\Integrations\Integration;
use Hostinger\Reach\Integrations\IntegrationInterface;
use Hostinger\Reach\Dto\PluginData;
use WPCF7_ContactForm;
use WPCF7_Submission;
use WP_Post;
use WPCF7_FormTag;

if ( ! DEFINED( 'ABSPATH' ) ) {
    exit;
}

class ContactForm7Integration extends Integration implements IntegrationInterface {

    public const INTEGRATION_NAME = 'contact-form-7';

    public function active_integration_hooks(): void {
        add_action( 'wpcf7_mail_sent', array( $this, 'handle_submission' ), 10, 1 );
    }

    public function handle_submission( WPCF7_ContactForm $contact_form ): void {
        if ( ! $this->is_form_enabled( $contact_form->id() ) ) {
            return;
        }

        $contact_list = $contact_form->title();
        $email        = $this->get_field_data( $contact_form, array( 'basetype' => 'email' ) );
        if ( $email ) {
            do_action(
                'hostinger_reach_submit',
                array(
                    'group'    => $contact_list,
                    'email'    => $email,
                    'metadata' => array(
                        'plugin'  => self::INTEGRATION_NAME,
                        'form_id' => $contact_form->id(),
                    ),
                )
            );
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

    public function get_plugin_data(): PluginData {
        return PluginData::from_array(
            array(
                'id'           => self::INTEGRATION_NAME,
                'title'        => __( 'Contact Form 7', 'hostinger-reach' ),
                'folder'       => 'contact-form-7',
                'file'         => 'wp-contact-form-7.php',
                'admin_url'    => 'admin.php?page=wpcf7',
                'add_form_url' => 'admin.php?page=wpcf7-new',
                'edit_url'     => 'admin.php?page=wpcf7&post={form_id}&action=edit',
                'url'          => 'https://wordpress.org/plugins/contact-form-7/',
                'download_url' => 'https://downloads.wordpress.org/plugin/contact-form-7.zip',
            )
        );
    }
}
