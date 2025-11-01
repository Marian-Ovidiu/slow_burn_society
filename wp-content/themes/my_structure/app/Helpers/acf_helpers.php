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

                $intents = $wpdb->get_results($sql, ARRAY_A) ?: [];
                echo App::blade()->make('optionPages.ordini', [
                    'orders'    => $intents,   // <--- passa le righe "grezze" della tabella
                    'actionUrl' => admin_url('admin-post.php'),
                ])->render();
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
// 1. Estrai primo paragrafo utile dai blocchi
function sbs_first_paragraph_from_blocks($post_id) {
  $post = get_post($post_id);
  if (!$post) return '';
  $blocks = parse_blocks($post->post_content);

  $walker = function($blocks) use (&$walker) {
    foreach ($blocks as $b) {
      if ($b['blockName'] === 'core/paragraph') {
        $html = render_block($b);
        $text = wp_strip_all_tags($html);
        if (trim($text) !== '') return $text;
      }
      if (!empty($b['innerBlocks'])) {
        $found = $walker($b['innerBlocks']);
        if ($found) return $found;
      }
    }
    return '';
  };

  return $walker($blocks) ?: '';
}

// 2. Estrai FAQ: preferisci blocchi core/details; fallback a sezione "FAQ" con H3 + paragrafi
function sbs_faq_from_blocks($post_id) {
  $post = get_post($post_id);
  if (!$post) return [];
  $blocks = parse_blocks($post->post_content);
  $faq = [];

  $scan = function($blocks) use (&$scan, &$faq) {
    $last_h = null;
    foreach ($blocks as $b) {
      // A) core/details → summary = domanda, inner content = risposta
      if ($b['blockName'] === 'core/details') {
        $summary = $b['attrs']['summary'] ?? '';
        if (!$summary) {
          // a seconda della versione WP il summary può essere dentro innerHTML
          $summary = wp_strip_all_tags($b['innerHTML'] ?? '');
          $summary = preg_replace('/\s+/', ' ', $summary);
        }
        $answerHtml = render_block($b);
        $answerText = wp_strip_all_tags(preg_replace('/^.*<\/summary>/s', '', $answerHtml));
        if ($summary && trim($answerText)) {
          $faq[] = ['q' => trim($summary), 'a' => trim($answerText)];
        }
      }

      // B) Pattern H3 + paragrafo immediato (sezione FAQ)
      if ($b['blockName'] === 'core/heading' && isset($b['attrs']['level']) && (int)$b['attrs']['level'] === 3) {
        $hText = wp_strip_all_tags(render_block($b));
        $last_h = trim($hText);
      } elseif ($last_h && $b['blockName'] === 'core/paragraph') {
        $pText = wp_strip_all_tags(render_block($b));
        if (trim($pText) !== '') {
          $faq[] = ['q' => $last_h, 'a' => trim($pText)];
          $last_h = null;
        }
      } else {
        $last_h = null;
      }

      if (!empty($b['innerBlocks'])) $scan($b['innerBlocks']);
    }
  };

  $scan($blocks);
  return $faq;
}

// 3. Estrai CTA prodotti da gruppi con classe .sbs-product
function sbs_products_from_blocks($post_id) {
  $post = get_post($post_id);
  if (!$post) return [];
  $blocks = parse_blocks($post->post_content);
  $out = [];

  $scan = function($blocks) use (&$scan, &$out) {
    foreach ($blocks as $b) {
      $class = $b['attrs']['className'] ?? '';
      if ($b['blockName'] === 'core/group' && is_string($class) && strpos($class, 'sbs-product') !== false) {
        $html = render_block($b);
        // fallback semplice via DOM: titolo (h4/h3), img, primo link
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $titleNode = $xpath->query('//h3|//h4')->item(0);
        $imgNode   = $xpath->query('//img')->item(0);
        $linkNode  = $xpath->query('//a[@href]')->item(0);

        $name = $titleNode ? trim($titleNode->textContent) : '';
        $img  = $imgNode ? $imgNode->getAttribute('src') : '';
        $url  = $linkNode ? $linkNode->getAttribute('href') : '';
        if ($name || $url) {
          $out[] = ['name'=>$name, 'image'=>$img, 'url'=>$url];
        }
      }
      if (!empty($b['innerBlocks'])) $scan($b['innerBlocks']);
    }
  };

  $scan($blocks);
  return $out;
}

// 4. Reading time auto con override opzionale via ACF (se proprio lo vuoi)
function sbs_reading_time_minutes($post_id) {
  $content = wp_strip_all_tags(get_post_field('post_content', $post_id));
  $words = str_word_count($content);
  $auto = max(1, (int) ceil($words / 220));
  if (function_exists('get_field')) {
    $ov = (int) get_field('reading_time_override', $post_id);
    if ($ov > 0) return $ov;
  }
  return $auto;
}
