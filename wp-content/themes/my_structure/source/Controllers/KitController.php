<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Kit;
use Classes\RelatedFactory;

class KitController extends BaseController
{
    public function archive()
    {
        $this->render('archivio-kit', []);
    }

    public function single()
    {
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        $slug = $this->resolveSlug();
        if (!$slug) return $this->render('404', [], 404);

        $k = Kit::findBySlug($slug);
        if (!$k)   return $this->render('404', [], 404);

        $kit       = $k->toView();
        $kitForJs  = $k->toJs();
        $items     = $k->itemsLite(); // opzionale: già dentro toJs() come 'products'
        $related   = RelatedFactory::forKit($k, ['min' => 4, 'max' => 4]);

        return $this->render('single-kit', [
            'kit'       => $kit,
            'kitForJs'  => $kitForJs,
            'items'     => $items,
            'related'   => $related,
        ]);
    }

    private function resolveSlug(): ?string
    {
        if (!empty($_GET['slug'])) return sanitize_title($_GET['slug']);
        $name = get_query_var('name');
        if (!empty($name)) return sanitize_title($name);
        $obj = get_queried_object();
        if ($obj && !empty($obj->post_name)) return sanitize_title($obj->post_name);
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if (preg_match('#/(?:kit|kits|bundle|bundles)/([^/]+)/?$#i', $path, $m)) return sanitize_title($m[1]);
        return null;
    }
}
