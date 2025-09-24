<?php

namespace Models;

use Core\Bases\BasePostType;

class Kit extends BasePostType
{
    public static $postType = 'kit';

    public $nome;
    public $mini_descrizione;
    public $descrizione;
    public $immagine_kit;  // ACF image array
    public $prodotti;      // ACF relationship (IDs o WP_Post[])
    public $prezzo;        // può arrivare con € e virgole
    public $disponibilita; // ACF “manuale” (opzionale)

    public function defineOtherAttributes($post)
    {
        $id = $this->id;
        $this->nome             = (string) get_field('nome', $id) ?: get_the_title($id);
        $this->mini_descrizione = (string) get_field('mini_descrizione', $id);
        $this->descrizione      = (string) get_field('descrizione', $id); // HTML
        $this->immagine_kit     = (array)  get_field('immagine_kit', $id) ?: [];
        $this->prodotti         = (array)  get_field('prodotti', $id) ?: [];
        $this->prezzo           = get_field('prezzo', $id);
        $this->disponibilita    = get_field('disponibilita', $id); // opzionale
    }

    // -------------------- Computed / helpers --------------------

    /** Prezzo numerico normalizzato */
    public function priceNumber(): float
    {
        $raw = (string) ($this->prezzo ?? '');
        $s = str_replace(['€', ' '], '', $raw);
        $s = str_replace(',', '.', $s);
        $n = (float) $s;
        return is_finite($n) ? $n : 0.0;
    }

    public function priceFormatted(): string
    {
        return number_format($this->priceNumber(), 2, ',', '.');
    }

    /** Immagine principale del kit (con fallback alla featured) */
    public function primaryImage(): string
    {
        if (!empty($this->immagine_kit['url'])) return (string) $this->immagine_kit['url'];
        return $this->featured_image ?: '';
    }

    /** Lista di oggetti Prodotto (caricati dai relationship) */
    public function products(): array
    {
        $out = [];
        foreach ((array)$this->prodotti as $p) {
            $pid = is_object($p) ? (int)($p->ID ?? $p->id ?? 0) : (int)$p;
            if ($pid > 0) {
                $pp = Prodotto::find($pid);
                if ($pp) $out[] = $pp;
            }
        }
        return $out;
    }

    /** Stock del kit = minimo stock tra i prodotti (o 0 se nessuno) */
    public function stock(): int
    {
        $products = $this->products();
        if (empty($products)) {
            // se c’è un campo manuale numerico lo usiamo, altrimenti 0
            return is_numeric($this->disponibilita) ? (int)$this->disponibilita : 0;
        }
        $min = null;
        foreach ($products as $p) {
            // usa la singola fonte di verità del modello Prodotto
            $s = (int)$p->stock();
            $min = ($min === null) ? $s : min($min, $s);
            if ($min <= 0) break;
        }
        // consentiamo override manuale se più restrittivo
        if (is_numeric($this->disponibilita)) {
            $manual = (int)$this->disponibilita;
            $min = ($min === null) ? $manual : min($min, $manual);
        }
        return (int) max(0, (int)$min);
    }

    public function isAvailable(): bool
    {
        return $this->stock() > 0;
    }

    public function descriptionHtml(): string
    {
        // preferisci descrizione ACF se presente, altrimenti content WP
        $html = $this->descrizione ?: apply_filters('the_content', (string)$this->content);
        return (string)$html;
    }

    /** Contenuti del kit in forma “leggera” per il frontend */
    public function itemsLite(): array
    {
        $items = [];
        foreach ($this->products() as $p) {
            // immagine del prodotto
            $img = '';
            $g = $p->galleryUrls();
            if (!empty($g)) $img = $g[0];

            $items[] = [
                'id'              => (int)$p->id,
                'title'           => (string)$p->title,
                'url'             => (string)$p->url,
                'immagine_1'      => is_array($p->immagine_1 ?? null) ? $p->immagine_1 : null,
                'image'           => $img,
                'short'           => '', // usa mini_descrizione se la tieni sui prodotti
                'price'           => (float)$p->priceNumber(),
                'price_formatted' => $p->priceFormatted(),
                'disponibilita'   => (int)$p->stock(),
                'available'       => $p->isAvailable(),
            ];
        }
        return $items;
    }

    /** Payload per il carrello */
    public function cartPayload(): array
    {
        return [
            'id'     => 'kit:' . (int)$this->id,
            'kitId'  => (int)$this->id,
            'type'   => 'kit',
            'name'   => (string)$this->title,
            'image'  => (string)$this->primaryImage(),
            'price'  => (float)$this->priceNumber(),
            'qty'    => 1,
            'maxQty' => (int)$this->stock(),
        ];
    }

    /** Array coerente per Blade */
    public function toView(): array
    {
        return [
            'id'               => (int)$this->id,
            'title'            => (string)$this->title,
            'pretitolo'        => (string)($this->mini_descrizione ?? ''), // o un campo dedicato
            'permalink'        => (string)$this->url,
            'description_html' => $this->descriptionHtml(),
            'descrizione'      => (string)($this->descrizione ?? ''),

            'price'            => $this->priceNumber(),
            'price_formatted'  => $this->priceFormatted(),

            'image'            => $this->primaryImage(),
            'gallery'          => [$this->primaryImage()], // per compatibilità

            'disponibilita'    => $this->stock(),
            'available'        => $this->isAvailable(),

            'cart'             => $this->cartPayload(),
        ];
    }

    /** Payload “ridotto” per JS / Alpine */
    public function toJs(): array
    {
        $v = $this->toView();
        return [
            'id'            => $v['id'],
            'title'         => $v['title'],
            'price'         => $v['price'],
            'image'         => $v['image'],
            'gallery'       => $v['gallery'],
            'description'   => $v['description_html'],
            'products'      => $this->itemsLite(),
            'disponibilita' => $v['disponibilita'],
            'cart'          => $v['cart'],
        ];
    }
}
