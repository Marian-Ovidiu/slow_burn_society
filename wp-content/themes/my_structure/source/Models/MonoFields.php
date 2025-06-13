<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class MonoFields extends BaseGroupAcf
{

    public $titolo_monologo;
    public $sottotitolo_monologo;
    public $immagine_monologo;

    public function __construct($postId = null) {
        parent::__construct('group_67d19021500c9', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        //Slider
        $this->addField('titolo_monologo');
        $this->addField('sottotitolo_monologo');
        $this->addField('immagine_monologo');
    }
}