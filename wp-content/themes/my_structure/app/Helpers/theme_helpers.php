<?php

if (!function_exists('my_theme_setup')) {
    function my_theme_setup() {
        add_base_js();
        add_base_css();
        register_menus();
    }
}

if (!function_exists('add_base_css')) {
    function add_base_css() {
        add_action('wp_enqueue_scripts', function() {
            $fullSrcStyle = vite_asset('scss/style.scss');
            wp_enqueue_style('style', $fullSrcStyle);
        });
    }
}

if (!function_exists('add_base_js')) {
    function add_base_js() {
        $fullSrc = vite_asset('js/main.js');
        add_action('wp_enqueue_scripts', function () use($fullSrc) {
            wp_enqueue_script('main', $fullSrc, ['jquery'], null, true);
            wp_script_add_data('main', 'data-iub-consent', 'necessary');
        });
    }
}

if (!function_exists('register_my_widgets')) {
    function register_my_widgets() {
        register_widget('Widget\MenuWidget');
    }
}

if (!function_exists('register_menus')) {
    function register_menus()
    {
        add_theme_support('menus');
        $menus = include get_template_directory() . '/app/Config/menus.php';
        register_nav_menus($menus);
    }
}


if (!function_exists('exclude_page_from_sitemap')) {
    function exclude_page_from_sitemap($url, $type, $object)
    {
        switch ($type){
            case 'category':
            case 'author':
                return false;
            default:
                return $url;
        }
    }
}