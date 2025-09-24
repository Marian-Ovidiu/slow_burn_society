<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Prodotto;
use Models\Kit;
use Classes\RelatedFactory;

class ProdottoController extends BaseController
{
    public function archive()
    {
        $this->render('archivio-prodotto', []);
    }

    public function single()
    {
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        $slug = $this->resolveSlug();
        if (!$slug) return $this->render('404', [], 404);

        $p = Prodotto::findBySlug($slug);
        if (!$p)     return $this->render('404', [], 404);

        $product      = $p->toView();
        $productForJs = $p->toJs();

        $related = \Classes\RelatedFactory::forProduct($p, ['min' => 4, 'max' => 4]);

        return $this->render('single-prodotto', [
            'product'       => $product,
            'productForJs'  => $productForJs,
            'relatedItems'  => $related,
        ]);
    }

    private function resolveSlug(): ?string
    {
        if (!empty($_GET['slug'])) {
            return sanitize_title($_GET['slug']);
        }
        $name = get_query_var('name');
        if (!empty($name)) {
            return sanitize_title($name);
        }
        $obj = get_queried_object();
        if ($obj && !empty($obj->post_name)) {
            return sanitize_title($obj->post_name);
        }
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if (preg_match('#/(?:prodotto|prodotti)/([^/]+)/?$#i', $path, $m)) {
            return sanitize_title($m[1]);
        }
        return null;
    }
}
