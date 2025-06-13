<?php
namespace Models;

use Core\Bases\BasePostType;

class Prodotto extends BasePostType
{
    public static $postType = 'prodotto';
    public $soluzioni_immagine_3_3;

    public function __construct($post = null)
    {
        parent::__construct($post);
    }

    public function defineOtherAttributes($post)
    {
        $this->soluzioni_immagine_3_3   = get_field('soluzioni_immagine_3_3', $this->id);
    }
}
