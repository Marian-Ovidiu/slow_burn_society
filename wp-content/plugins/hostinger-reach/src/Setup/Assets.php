<?php

namespace Hostinger\Reach\Setup;

use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Functions;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Assets {
    private Functions $functions;
    private ReachApiHandler $reach_api_handler;

    public function __construct( Functions $functions, ReachApiHandler $reach_api_handler ) {
        $this->functions         = $functions;
        $this->reach_api_handler = $reach_api_handler;
    }

    public function init(): void {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    public function admin_enqueue_scripts(): void {

        if ( ! $this->functions->need_to_load_assets() ) {
            return;
        }

        if ( file_exists( $this->functions->get_frontend_dir() . 'main.js' ) === false ) {
            return;
        }

        wp_enqueue_script(
            'hostinger-reach',
            $this->functions->get_frontend_url() . 'main.js',
            array(),
            filemtime( $this->functions->get_frontend_dir() . 'main.js' ),
            true
        );

        $css_file = $this->functions->get_frontend_dir() . 'main.css';
        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'hostinger-reach-styles',
                $this->functions->get_frontend_url() . 'main.css',
                array(),
                filemtime( $css_file )
            );
        }

        wp_localize_script(
            'hostinger-reach',
            'hostinger_reach_reach_data',
            array(
                'site_url'          => get_site_url(),
                'rest_base_url'     => esc_url_raw( rest_url() ),
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'wp_rest' ),
                'plugin_url'        => HOSTINGER_REACH_PLUGIN_URL,
                'translations'      => $this->get_translations(),
                'is_connected'      => $this->reach_api_handler->is_connected(),
                'is_hostinger_user' => $this->functions->is_hostinger_user(),
                'is_staging'        => $this->functions->is_staging(),
            )
        );
    }

    private function get_translations(): array {
        return array(
            'hostinger_reach_error_message'                           => __( 'Something went wrong', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_title'                      => __( 'Welcome to Reach', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_description'                => __( 'Create email campaigns using AI-crafted templates that match your style. Instantly sync with your WordPress site and connect with your audience easily.', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_connection_warning'         => __( 'Reach is already connected to another site.', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_connection_instruction'     => __( 'Disconnect it to link this site instead.', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_manage_button'              => __( 'Manage', 'hostinger-reach' ),
            'hostinger_reach_welcome_view_start_button'               => __( 'Connect site', 'hostinger-reach' ),
            'hostinger_reach_header_go_to_reach_button'               => __( 'Go to Reach', 'hostinger-reach' ),
            'hostinger_reach_header_logo_alt'                         => __( 'Hostinger Reach', 'hostinger-reach' ),
            'hostinger_reach_hero_background_alt'                     => __( 'Email marketing background with gradient design', 'hostinger-reach' ),
            'hostinger_reach_hero_overlay_alt'                        => __( 'Email marketing illustration featuring envelopes and communication icons', 'hostinger-reach' ),
            'hostinger_reach_overview_title'                          => __( 'This month', 'hostinger-reach' ),
            'hostinger_reach_overview_your_plan_button'               => __( 'Your plan', 'hostinger-reach' ),
            'hostinger_reach_overview_upgrade_button'                 => __( 'Upgrade', 'hostinger-reach' ),
            'hostinger_reach_overview_emails_title'                   => __( 'Emails', 'hostinger-reach' ),
            'hostinger_reach_overview_emails_sent_label'              => __( 'Sent', 'hostinger-reach' ),
            'hostinger_reach_overview_emails_remaining_label'         => __( 'Remaining', 'hostinger-reach' ),
            'hostinger_reach_overview_campaigns_title'                => __( 'Campaigns', 'hostinger-reach' ),
            'hostinger_reach_overview_campaigns_sent_label'           => __( 'Sent', 'hostinger-reach' ),
            'hostinger_reach_overview_campaigns_ctor_label'           => __( 'Average CTOR', 'hostinger-reach' ),
            'hostinger_reach_overview_subscribers_title'              => __( 'Subscribers', 'hostinger-reach' ),
            'hostinger_reach_overview_subscribers_new_label'          => __( 'New subscribers', 'hostinger-reach' ),
            'hostinger_reach_overview_subscribers_unsubscribes_label' => __( 'Unsubscribes', 'hostinger-reach' ),
            'hostinger_reach_overview_subscribers_total_label'        => __( 'Total subscribers', 'hostinger-reach' ),
            'hostinger_reach_overview_campaigns_text'                 => __( 'Create campaign', 'hostinger-reach' ),
            'hostinger_reach_overview_templates_text'                 => __( 'Create template', 'hostinger-reach' ),
            'hostinger_reach_overview_settings_text'                  => __( 'Settings', 'hostinger-reach' ),
            'hostinger_reach_forms_title'                             => __( 'Forms', 'hostinger-reach' ),
            'hostinger_reach_forms_card_title'                        => __( 'Start collecting form submissions', 'hostinger-reach' ),
            'hostinger_reach_forms_card_description'                  => __( 'Choose how you\'d like to manage forms on your website. Create a new form with Hostinger Reach or connect a third-party plugin.', 'hostinger-reach' ),
            'hostinger_reach_forms_card_button_text'                  => __( 'Add form', 'hostinger-reach' ),
            'hostinger_reach_forms_add_more_button_text'              => __( 'Add form', 'hostinger-reach' ),
            'hostinger_reach_forms_new_page_text'                     => __( 'New page', 'hostinger-reach' ),
            'hostinger_reach_forms_no_pages_available'                => __( 'No pages available. Create a new page to get started.', 'hostinger-reach' ),
            'hostinger_reach_faq_title'                               => __( 'FAQ', 'hostinger-reach' ),
            'hostinger_reach_faq_what_is_reach_question'              => __( 'What is Hostinger Reach email marketing service?', 'hostinger-reach' ),
            'hostinger_reach_faq_what_is_reach_answer'                => __( 'Hostinger Reach is an AI-powered email marketing tool for small businesses and creators. It supports your entire email marketing journey—from building contact lists to sending campaigns and tracking results.', 'hostinger-reach' ),
            'hostinger_reach_faq_how_different_question'              => __( 'How is Hostinger Reach different from other email marketing apps?', 'hostinger-reach' ),
            'hostinger_reach_faq_how_different_answer'                => __( 'Hostinger Reach is built for simplicity, speed, and results – no design or marketing experience needed. Unlike most email tools, at the core of Reach is its AI-powered template creator. Whether it is a product launch, special offer, or newsletter update, it instantly crafts a professional, mobile-friendly email. It not only writes the content for you; it also suggests the best layout for your message and saves your style settings so you\'re never starting from scratch.<br><br>Every template is customizable, so your emails reflect your brand\'s look, feel, and voice. And because the templates are built using proven best practices, they\'re optimized for readability, accessibility, and reader engagement.', 'hostinger-reach' ),
            'hostinger_reach_faq_how_much_cost_question'              => __( 'How much does it cost to use Hostinger Reach?', 'hostinger-reach' ),
            'hostinger_reach_faq_how_much_cost_answer'                => __( 'Reach offers a <b>free plan</b> for one year– perfect for getting started. Paid plans are based on how many unique contacts you aim to reach and how many emails you send monthly. As your audience grows, you can upgrade to a plan that fits your needs. Reach does not limit your contact list, so you don\'t need to worry about lost data and can consistently grow your audience.', 'hostinger-reach' ),
            'hostinger_reach_ui_opens_in_new_tab'                     => __( 'opens in new tab', 'hostinger-reach' ),
            'hostinger_reach_ui_banner_background_image'              => __( 'Banner background image for', 'hostinger-reach' ),
            'hostinger_reach_ui_background_image_for'                 => __( 'Background image for', 'hostinger-reach' ),
            'hostinger_reach_ui_usage_statistics'                     => __( 'usage statistics', 'hostinger-reach' ),
            'hostinger_reach_ui_tooltip_ctor_info'                    => __( 'Click-to-open rate tells you what percent of opens resulted in a click too. A good CTOR is 6-17%, depending on your industry.', 'hostinger-reach' ),
            'hostinger_reach_forms_modal_title'                       => __( 'Select page', 'hostinger-reach' ),
            'hostinger_reach_add_form_modal_title'                    => __( 'Add form', 'hostinger-reach' ),
            'hostinger_reach_confirm_disconnect_modal_title'          => __( 'Disconnect plugin?', 'hostinger-reach' ),
            'hostinger_reach_confirm_disconnect_modal_text'           => __( 'Disconnecting will stop new contacts from being collected. You can reconnect or use a different form anytime.', 'hostinger-reach' ),
            'hostinger_reach_confirm_disconnect_modal_cancel'         => __( 'Cancel ', 'hostinger-reach' ),
            'hostinger_reach_confirm_disconnect_modal_disconnect'     => __( 'Disconnect ', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_plugin_header'      => __( 'Plugin', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_entries_header'     => __( 'Entries', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_status_header'      => __( 'Status', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_status_active'      => __( 'Active', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_status_inactive'    => __( 'Inactive', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_view_form'          => __( 'View form', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_edit_form'          => __( 'Edit form', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_go_to_plugin'       => __( 'Go to plugin', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_disconnect_plugin'  => __( 'Disconnect plugin', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_add_form'           => __( 'Add form', 'hostinger-reach' ),
            'hostinger_reach_plugin_titles_hostinger_reach'           => __( 'Hostinger Reach', 'hostinger-reach' ),
            'hostinger_reach_plugin_titles_contact_form_7'            => __( 'Contact Form 7', 'hostinger-reach' ),
            'hostinger_reach_plugin_titles_wp_forms_lite'             => __( 'WP Forms Lite', 'hostinger-reach' ),
            'hostinger_reach_plugin_titles_elementor'                 => __( 'Elementor', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_expand_aria'        => __( 'Expand {pluginName} details', 'hostinger-reach' ),
            'hostinger_reach_plugin_entries_table_collapse_aria'      => __( 'Collapse {pluginName} details', 'hostinger-reach' ),
            'hostinger_reach_plugin_expansion_no_forms'               => __( 'No forms found for this integration.', 'hostinger-reach' ),
            'hostinger_reach_forms_no_title'                          => __( '(no title)', 'hostinger-reach' ),
            'hostinger_reach_forms_plugin_connected_success'          => __( 'Plugin connected successfully', 'hostinger-reach' ),
            'hostinger_reach_forms_plugin_disconnected_success'       => __( 'Plugin disconnected successfully', 'hostinger-reach' ),
            'hostinger_reach_forms_active'                            => __( 'Active', 'hostinger-reach' ),
            'hostinger_reach_forms_consent_notice'                    => __( 'Make sure the people you contact expect your emails and are okay with receiving them.', 'hostinger-reach' ),
            'hostinger_reach_forms_new_contact_form'                  => __( 'New contact form', 'hostinger-reach' ),
            'hostinger_reach_forms_create_form_button'                => __( 'Create form', 'hostinger-reach' ),
            'hostinger_reach_forms_view_supported_plugins'            => __( 'View supported plugins', 'hostinger-reach' ),
            'hostinger_reach_forms_installed_plugins'                 => __( 'Installed plugins', 'hostinger-reach' ),
            'hostinger_reach_forms_install_and_connect'               => __( 'Install and connect', 'hostinger-reach' ),
            'hostinger_reach_forms_disconnect'                        => __( 'Disconnect', 'hostinger-reach' ),
            'hostinger_reach_forms_connect'                           => __( 'Connect', 'hostinger-reach' ),
        );
    }
}
