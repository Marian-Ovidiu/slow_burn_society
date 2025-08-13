<?php

namespace Models;

use Core\Bases\BasePostType;

class Prodotto extends BasePostType
{
    public static $postType = 'prodotto';

    public $pretitolo;
    public $immagine_1;
    public $immagine_2;
    public $immagine_3;
    public $immagine_4;
    public $titolo_descrizione;
    public $descrizione;
    public $prezzo;
    public $categoria;
    public $disponibilita;

    public function __construct($post = null)
    {
        parent::__construct($post);
    }

    public function defineOtherAttributes($post)
    {
        $this->pretitolo   = get_field('pretitolo', $this->id);
        $this->immagine_1   = get_field('immagine_1', $this->id);
        $this->immagine_2   = get_field('immagine_2', $this->id);
        $this->immagine_3   = get_field('immagine_3', $this->id);
        $this->immagine_4   = get_field('immagine_4', $this->id);
        $this->titolo_descrizione   = get_field('titolo_descrizione', $this->id);
        $this->descrizione   = get_field('descrizione', $this->id);
        $this->prezzo   = get_field('prezzo', $this->id);
        $this->categoria   = get_field('categoria', $this->id);
        $this->disponibilita   = get_field('disponibilita', $this->id);
    }
}
