<?php

use Core\App;

if (!function_exists('my_custom_options_page')) {
    function my_custom_options_page()
    {
        add_menu_page(
            'Ordini',
            'Ordini',
            'manage_options',
            'opzioni-ordini',
            function () {
                my_custom_options_page_html('ordini');
            }
        );
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
            case 'ordini':

                global $wpdb;
                $table = $wpdb->prefix . 'sbs_payment_intents';

                // --- Filtro ricerca (senza paginazione) ---
                $search  = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
                $where   = '1=1';
                $params  = [];
                if ($search !== '') {
                    $like   = '%' . $wpdb->esc_like($search) . '%';
                    $where .= ' AND (intent_id LIKE %s OR email LIKE %s)';
                    $params[] = $like;
                    $params[] = $like;
                }

                // --- Query TUTTI gli intents (no LIMIT/OFFSET) ---
                $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC";
                if (!empty($params)) {
                    $sql = $wpdb->prepare($sql, ...$params);
                }
                $intents = $wpdb->get_results($sql, ARRAY_A) ?: [];

                // --- Helpers inline (server-side) ---
                $resolveItem = function (?int $pid, ?int $kitId): array {
                    $isKit  = false;
                    $postId = 0;
                    if (!empty($pid)) {
                        $postId = (int) $pid;
                    } elseif (!empty($kitId)) {
                        $isKit  = true;
                        $postId = (int) $kitId;
                    }
                    $title = $postId ? get_the_title($postId) : '';
                    if (!$title) $title = $isKit ? 'Kit senza titolo' : 'Prodotto sconosciuto';

                    $thumb = $postId ? get_the_post_thumbnail_url($postId, 'thumbnail') : '';

                    return [
                        'isKit'   => $isKit,
                        'postId'  => $postId,
                        'title'   => $title,
                        'thumb'   => $thumb ?: '',
                        'editUrl' => $postId ? get_edit_post_link($postId, '') : '',
                    ];
                };

                // --- Appiattisci intents -> righe item ---
                $rows = [];
                foreach ($intents as $row) {
                    $items = json_decode($row['items_json'] ?? '[]', true);
                    if (!is_array($items) || empty($items)) {
                        $rows[] = [
                            'intent_id'   => $row['intent_id'],
                            'created_at'  => $row['created_at'],
                            'email'       => $row['email'],
                            'pay_status'  => $row['status'],
                            'ship_status' => $row['shipping_status'] ?? null,
                            'product_id'  => null,
                            'product'     => [
                                'isKit'   => false,
                                'postId'  => 0,
                                'title'   => '(nessun articolo)',
                                'thumb'   => '',
                                'editUrl' => '',
                            ],
                            'qty'         => null,
                        ];
                        continue;
                    }
                    foreach ($items as $it) {
                        $pid   = isset($it['id']) ? (int) $it['id'] : 0;
                        $kitId = isset($it['kitId']) ? (int) $it['kitId'] : 0;
                        $qty   = isset($it['qty']) ? (int) $it['qty'] : 1;

                        $prod = $resolveItem($pid, $kitId);
                        $rows[] = [
                            'intent_id'   => $row['intent_id'],
                            'created_at'  => $row['created_at'],
                            'email'       => $row['email'],
                            'pay_status'  => $row['status'],
                            'ship_status' => $row['shipping_status'] ?? null,
                            'product_id'  => $prod['postId'],
                            'product'     => $prod,
                            'qty'         => max(1, $qty),
                        ];
                    }
                }

                // Dati per la view (no paginazione)
                $data = [
                    'rows'      => $rows,
                    'search'    => $search,
                    'actionUrl' => admin_url('admin-post.php'),
                ];

                echo App::blade()->make('optionPages.ordini', $data)->render();
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
        $choices['opzioni-ordini'] = 'Ordini';
        return $choices;
    }
}

function my_acf_location_options_page($match, $rule, $options)
{
    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case 'opzioni-generali':
            case 'opzioni-prodotto':
            case 'opzioni-ordini':
                $match = true;
                break;
        }
    }
    return $match;
}
