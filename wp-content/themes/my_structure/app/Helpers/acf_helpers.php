<?php

use Core\App;

if (!function_exists('my_custom_options_page')) {
    function my_custom_options_page()
    {
        add_menu_page(
            'Impostazioni Generali',
            'Opzioni Generali',
            'manage_options',
            'opzioni-generali',
            function () {
                my_custom_options_page_html('generali');
            }
        );
        add_menu_page(
            'Opzioni Prodotto',
            'Opzioni Prodotto',
            'manage_options',
            'opzioni-prodotto',
            function () {
                my_custom_options_page_html('prodotto');
            }
        );
    }
}

if (!function_exists('my_custom_options_page_html')) {
    
    function my_custom_options_page_html($page)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        do_action('admin_head'); // Carica stili admin
        do_action('admin_enqueue_scripts'); // Carica script admin



        echo '<div class="wrap">';
        switch ($page) {
            case 'generali':
                echo App::blade()->make('optionPages.generals', [])->render();
                break;
            case 'prodotto':
                echo App::blade()->make('optionPages.generalsProdotto', [])->render();
                break;
        }

        echo '</div>';

        do_action('admin_footer');
    }
}

if (!function_exists('acf_location_rules_types')) {
    function acf_location_rules_types($choices)
    {
        $choices['Basic']['page'] = 'Pagina Opzioni';
        return $choices;
    }
}

if (!function_exists('acf_location_rule_values_page')) {
    function acf_location_rule_values_page($choices)
    {
        $choices['opzioni-generali'] = 'Opzioni Generali';
        $choices['opzioni-prodotto'] = 'Opzioni Prodotto';
        return $choices;
    }
}

function my_acf_location_options_page($match, $rule, $options)
{
    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case 'opzioni-generali':
            case 'opzioni-prodotto':
                $match = true;
                break;
        }
    }
    return $match;
}
