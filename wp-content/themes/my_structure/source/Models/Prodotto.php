<?php

namespace Models;

use Core\Bases\BasePostType;
use WP_Term;

class Prodotto extends BasePostType
{
    public static $postType = 'prodotto';

    public $pretitolo;
    public $immagine_1; public $immagine_2; public $immagine_3; public $immagine_4;
    public $titolo_descrizione;
    public $descrizione;
    public $prezzo;
    public $categoria;      // ACF (può essere WP_Term/array/id/string)
    public $disponibilita;  // ACF base (fallback se non c'è sbs_inventory)

    public function defineOtherAttributes($post)
    {
        $id = $this->id;
        $this->pretitolo         = get_field('pretitolo', $id);
        $this->immagine_1        = get_field('immagine_1', $id);
        $this->immagine_2        = get_field('immagine_2', $id);
        $this->immagine_3        = get_field('immagine_3', $id);
        $this->immagine_4        = get_field('immagine_4', $id);
        $this->titolo_descrizione= get_field('titolo_descrizione', $id);
        $this->descrizione       = get_field('descrizione', $id);
        $this->prezzo            = get_field('prezzo', $id);
        $this->categoria         = get_field('categoria', $id);
        $this->disponibilita     = get_field('disponibilita', $id);
    }

    // -------- Computed / helpers ------------------------------------------

    public function priceNumber(): float {
        return $this->toFloat($this->prezzo ?? 0);
    }

    public function priceFormatted(): string {
        return number_format($this->priceNumber(), 2, ',', '.');
    }

    public function categorySlug(): string {
        $c = $this->categoria;
        if (empty($c)) return '';
        if (is_string($c)) return $c;
        if ($c instanceof WP_Term) return $c->slug ?: ($c->name ?? '');
        if (is_array($c)) {
            $first = reset($c);
            if ($first instanceof WP_Term) return $first->slug ?: ($first->name ?? '');
            if (is_numeric($first)) { $t = get_term((int)$first); return $t && !is_wp_error($t) ? ($t->slug ?: ($t->name ?? '')) : ''; }
            if (is_string($first)) return $first;
        }
        if (is_object($c)) return $c->slug ?? ($c->name ?? '');
        return '';
    }

    public function galleryUrls(): array {
        $urls = [];
        foreach (['immagine_1','immagine_2','immagine_3','immagine_4'] as $k) {
            $v = $this->$k ?? null;
            if (is_array($v) && !empty($v['url']))      $urls[] = $v['url'];
            elseif (is_string($v) && filter_var($v, FILTER_VALIDATE_URL)) $urls[] = $v;
        }
        if (!$urls && $this->featured_image) $urls[] = $this->featured_image;
        return array_values(array_unique(array_filter($urls)));
    }

    public function primaryImage(): string {
        $g = $this->galleryUrls();
        return $g[0] ?? ($this->featured_image ?? '');
    }

    public function stock(): int {
        global $wpdb;
        $tbl = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1", (int)$this->id
        ));
        return ($tbl !== null) ? (int)$tbl : (int)($this->disponibilita ?? 0);
    }

    public function isAvailable(): bool { return $this->stock() > 0; }

    public function descriptionHtml(): string {
        // descrizione custom se presente, altrimenti content WP filtrato
        $html = $this->descrizione ?: apply_filters('the_content', (string)$this->content);
        return (string)$html;
    }

    public function cartPayload(): array {
        return [
            'id'        => (int)$this->id,
            'productId' => (int)$this->id,
            'type'      => 'product',
            'name'      => (string)$this->title,
            'image'     => (string)$this->primaryImage(),
            'price'     => (float)$this->priceNumber(),
            'qty'       => 1,
            'maxQty'    => max(0, (int)$this->stock()),
        ];
    }

    /** Array coerente per Blade */
    public function toView(): array {
        return [
            'id'                => (int)$this->id,
            'slug'              => '', // opzionale se vuoi passarla
            'title'             => (string)$this->title,
            'pretitolo'         => (string)($this->pretitolo ?? ''),
            'permalink'         => (string)$this->url,
            'titolo_descrizione'=> (string)($this->titolo_descrizione ?? ''),
            'descrizione'       => (string)($this->descrizione ?? ''),
            'description_html'  => $this->descriptionHtml(),
            'categoria'         => $this->categorySlug(),
            'price'             => $this->priceNumber(),
            'price_formatted'   => $this->priceFormatted(),
            'image'             => $this->primaryImage(),
            'gallery'           => $this->galleryUrls(),
            'disponibilita'     => $this->stock(),
            'available'         => $this->isAvailable(),
            'cart'              => $this->cartPayload(),
        ];
    }

    /** Payload “ridotto” per JS */
    public function toJs(): array {
        $v = $this->toView();
        return [
            'id'            => $v['id'],
            'title'         => $v['title'],
            'price'         => $v['price'],
            'image'         => $v['image'],
            'gallery'       => $v['gallery'],
            'description'   => $v['description_html'], // HTML già sanificato
            'disponibilita' => $v['disponibilita'],
            'cart'          => $v['cart'],
        ];
    }

    // --- util ---------------------------------------------------------------
    private function toFloat($val): float {
        if (is_numeric($val)) return (float)$val;
        $s = (string)($val ?? '');
        $s = preg_replace('/[^\d,\.]/', '', $s);
        $s = str_replace(',', '.', $s);
        $n = (float)$s;
        return is_finite($n) ? $n : 0.0;
    }
}
