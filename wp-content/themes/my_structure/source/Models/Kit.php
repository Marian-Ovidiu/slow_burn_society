<?php

namespace Models;

use Core\Bases\BasePostType;

/**
 * @property int    $id
 * @property string $nome
 * @property string $mini_descrizione
 * @property string $descrizione
 * @property array  $immagine_kit  // ACF image array
 * @property array  $prodotti      // ACF relationship (IDs o WP_Post[])
 * @property mixed  $prezzo        // può arrivare con € e virgole
 */
class Kit extends BasePostType
{
    public static $postType = 'kit';

    public $nome;
    public $mini_descrizione;
    public $descrizione;
    public $immagine_kit;
    public $prodotti;
    public $prezzo;
    public $disponibilita;

    public function __construct($post = null)
    {
        parent::__construct($post);
    }

    public function defineOtherAttributes($post)
    {
        $id = $this->id;

        $this->nome             = (string) get_field('nome', $id) ?: get_the_title($id);
        $this->mini_descrizione = (string) get_field('mini_descrizione', $id);
        $this->descrizione      = (string) get_field('descrizione', $id); // può contenere HTML
        $this->immagine_kit     = (array)  get_field('immagine_kit', $id) ?: [];
        $this->prodotti         = (array)  get_field('prodotti', $id) ?: [];
        $this->prezzo           = get_field('prezzo', $id);
        $this->disponibilita    = $this->computeAvailability();
    }

    public function computeAvailability(): bool
    {
        if (empty($this->prodotti)) return false;

        foreach ($this->prodotti as $p) {
            if (!$this->productAvailable($p)) { // <-- niente "!"
                return false;
            }
        }
        return true;
    }

    private function productAvailable($product): bool
    {
        $pid = is_object($product) ? (int) ($product->ID ?? 0) : (int) $product;
        if ($pid <= 0) return false;

        $prodotto = Prodotto::find($pid);
        if (!$prodotto) return false;

        $raw = $prodotto->disponibilita;

        if (is_numeric($raw)) return ((int)$raw) > 0;
        if (is_bool($raw))    return $raw;
        if (is_string($raw)) {
            $v = trim(mb_strtolower($raw));
            return in_array($v, ['1', 'true', 'si', 'sì', 'disponibile', 'available'], true);
        }
        return false;
    }

    private function productStock($product): int
    {
        $pid = is_object($product) ? (int) ($product->ID ?? 0) : (int) $product;
        if ($pid <= 0) return false;

        $prodotto = Prodotto::find($pid);

        if ($prodotto) {
            $rawDispon = $prodotto->disponibilita;
            if (is_numeric($rawDispon)) {
                return $rawDispon == 0 ? false : true;
            }
        }

        return false;
    }

    /** URL immagine principale (safe) */
    public function imageUrl(): string
    {
        if (is_array($this->immagine_kit) && !empty($this->immagine_kit['url'])) {
            return esc_url($this->immagine_kit['url']);
        }
        return '';
    }

    /** Prezzo in float (normalizza € e virgole) */
    public function priceFloat(): float
    {
        $raw = (string) $this->prezzo;

        $clean = str_replace(['€', ' '], '', $raw);

        // se contiene la virgola come separatore decimale, sostituiscila con punto
        // ma non toccare i separatori migliaia . perché spesso non ci sono
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }

    /** Prezzo formattato “italiano”: 1.234,56 */
    public function priceFormatted(): string
    {
        return number_format($this->priceFloat(), 2, ',', '.');
    }

    /** Mappa i prodotti (relationship) in un payload leggero per il frontend */
    public function productsLite(): array
    {
        if (empty($this->prodotti)) return [];

        return array_values(array_map(function ($p) {
            // $p può essere WP_Post o ID
            $pid   = is_object($p) ? $p->ID : (int) $p;
            $title = is_object($p) ? ($p->post_title ?? '') : (get_the_title($pid) ?: '');

            $imgArr = (array) get_field('immagine_1', $pid);
            $imgUrl = !empty($imgArr['url']) ? esc_url($imgArr['url']) : '';

            return [
                'title' => $title ?: '',
                'image' => $imgUrl,
            ];
        }, $this->prodotti));
    }

    /** Payload “pronto Alpine” (solo quello che ti serve in JS) */
    public function toFrontendArray(): array
    {
        return [
            'id'          => (int) $this->id,
            'title'       => (string) $this->nome,
            'description' => (string) $this->descrizione, // può essere mostrata con x-html
            'image'       => $this->imageUrl(),
            'price'       => $this->priceFloat(),         // numero: comodo in JS
            'products'    => $this->productsLite(),
            'disponibilita' => $this->disponibilita,
        ];
    }

    /** JSON per data-attributes (safe per Blade/Alpine) */
    public function toFrontendJson(): string
    {
        // wp_json_encode gestisce correttamente charset e escaping HTML
        return wp_json_encode($this->toFrontendArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Helpers di query */

    public static function latest(int $limit = 6): array
    {
        $q = new \WP_Query([
            'post_type'      => static::$postType,
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ]);

        $items = [];
        foreach ($q->posts as $post) {
            $items[] = new static($post);
        }
        wp_reset_postdata();
        return $items;
    }
}
