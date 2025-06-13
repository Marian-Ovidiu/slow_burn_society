<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class DuoFields extends BaseGroupAcf
{

    public $titolo_monologo;
    public $sottotitolo_monologo;
    public $immagine_monologo;

    public function __construct($postId = null) {
        parent::__construct('group_67ecfc307a8f5', $postId ?: get_the_ID());
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        //Slider
        $this->addField('titolo_duo_logo_1');
        $this->addField('sottotitolo_duo_logo_1');
        $this->addField('immagine_duo_logo_1');+
        $this->addField('titolo_duo_logo_2');
        $this->addField('sottotitolo_duo_logo_2');
        $this->addField('immagine_duo_logo_2');
    }
}