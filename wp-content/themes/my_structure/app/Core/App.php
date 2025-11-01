<?php

namespace Core;

class App extends Init
{
    public function init()
    {
        parent::init();
        $this->registerHook();
        $this->registerFilters();
        $this->registerProviders();
    }

    public function registerHook()
    {
        add_action('after_setup_theme', 'my_theme_setup');
        add_action('after_setup_theme', function () {
            add_theme_support('wp-block-styles');     // stili base blocchi
            add_theme_support('align-wide');          // allineamento wide/full
            add_theme_support('responsive-embeds');   // embed responsive
            add_theme_support('editor-styles');
            add_theme_support('post-thumbnails', ['post']); // aggiungi CPT se vuoi
            add_image_size('sbs-card', 800, 450, true);     // 16:9 per card
            add_image_size('sbs-hero', 1600, 900, true);    // 16:9 per hero      // CSS custom nell’editor
        });
        add_action('widgets_init', 'register_my_widgets');
        add_action('admin_menu', 'my_custom_options_page');
        add_action('load-toplevel_page_opzioni-generali', 'acf_form_head');
        add_action('load-toplevel_page_opzioni-prodotto', 'acf_form_head');
        add_action('admin_enqueue_scripts', function ($hook) {
            if (in_array($hook, ['toplevel_page_opzioni-generali', 'toplevel_page_opzioni-prodotto'])) {
                acf_enqueue_scripts();
            }
        });
        add_action('phpmailer_init', 'phpmailer_init_sbs');
        add_action('wp_mail_failed', function ($err) {
            error_log('[MAIL FAILED] ' . print_r($err->get_error_message(), true));
        });
    }

    public function registerFilters()
    {
        add_filter('woocommerce_get_page_id', 'disable_woocommerce_pages');
        add_filter('acf/location/rule_types', 'acf_location_rules_types');
        add_filter('acf/location/rule_values/page', 'acf_location_rule_values_page');
        add_filter('acf/location/rule_match/page', 'my_acf_location_options_page', 10, 3);
        add_filter('wpseo_sitemap_entry', 'exclude_page_from_sitemap', 10, 3);
        add_filter('rest_enabled', '__return_false');
    }

    public function registerProviders()
    {
        $providers = require get_template_directory() . '/app/Config/providers.php';
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $provider = new $provider();
                $provider->register();
            }
        }
    }
}
