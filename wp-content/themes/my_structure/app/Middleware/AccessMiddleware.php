<?php

namespace Middleware;

use Controllers\PageController;

class AccessMiddleware
{
    public static function handle(string $uri): bool
    {
        // Path pulito (senza query)
        $clean = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Normalizza slash finale (tranne home)
        $clean = rtrim($clean, '/');
        if ($clean === '') $clean = '/';

        // 1) WHITELIST CORE WORDPRESS (backoffice, api, asset, file speciali)
        $allowedExact = [
            '/',                           // home
            '/contatti',                   // contatti
            '/wp-login.php',
            '/wp-cron.php',
            '/xmlrpc.php',
            '/robots.txt',
            '/grazie',                     // tua pagina
        ];

        $allowedPrefixes = [
            '/wp-admin',
            '/admin',
            '/manage-site',                   // include /wp-admin/admin-ajax.php
            '/wp-includes',
            '/wp-content',                 // temi, plugin, uploads
            '/wp-json',                    // REST API
        ];

        $allowedRegex = [
            '#^/sitemap.*\.xml$#i',       // sitemaps vari
            '#^/feed/?$#i',               // feed
            '#^/comments/feed/?$#i',
        ];

        // 2) TUE ROUTE TECNICHE (checkout/stripe/cart/related…)
        $allowedBusinessExact = [
            '/checkout',
            '/create-payment-intent',
            '/update-intent-details',
            '/webhooks/stripe',
            '/checkout/finalize',
            '/cart',
            '/cart/save',
            '/cart/event',
            '/related',
        ];

        // Se è in whitelist "exact"
        if (in_array($clean, array_merge($allowedExact, $allowedBusinessExact), true)) {
            return true;
        }

        // Se inizia con prefix consentiti
        foreach ($allowedPrefixes as $pfx) {
            if (str_starts_with($clean, $pfx)) {
                return true;
            }
        }

        // Se matcha regex consentite
        foreach ($allowedRegex as $rx) {
            if (preg_match($rx, $clean)) {
                return true;
            }
        }

        // 3) ARTICOLI (post pubblicati): /slug o /categoria/slug (gestito da WP)
        // Togli primo slash
        $slug = ltrim($clean, '/');

        // Tenta match diretto del path completo come pagina/post
        $post = get_page_by_path($slug, OBJECT, 'post');
        if ($post && $post->post_status === 'publish') {
            return true;
        }

        // Tenta ultimo segmento come slug (per permalink complessi)
        $last = basename($slug);
        if ($last && $last !== $slug) {
            $post2 = get_page_by_path($last, OBJECT, 'post');
            if ($post2 && $post2->post_status === 'publish') {
                return true;
            }
        }

        // ❌ Tutto il resto → 404 Blade
        http_response_code(404);
        if (function_exists('status_header')) status_header(404);

        $controller = new \Controllers\PageController();
        $controller->page404();
        exit;
    }
}
