<?php

namespace Models;

use Core\Bases\BasePostType;

class Kit extends BasePostType
{
    public static $postType = 'kit';

    public $nome;
    public $mini_descrizione;
    public $descrizione;
    public $immagine_kit;
    public $prodotti;
    public $prezzo;

    public function __construct($post = null)
    {
        parent::__construct($post);
    }

    public function defineOtherAttributes($post)
    {
        $this->nome   = get_field('nome', $this->id);
        $this->mini_descrizione   = get_field('mini_descrizione', $this->id);
        $this->descrizione   = get_field('descrizione', $this->id);
        $this->immagine_kit   = get_field('immagine_kit', $this->id);
        $this->prodotti   = get_field('prodotti', $this->id);
        $this->prezzo   = get_field('prezzo', $this->id);
    }
}
