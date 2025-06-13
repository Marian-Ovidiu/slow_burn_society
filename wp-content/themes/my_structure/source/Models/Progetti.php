<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class Progetti extends BaseGroupAcf
{
    public $immagine_hero;
    public $titolo_hero;
    public $highlights_frase_1;
    public $highlights_frase_2;
    public $highlights_frase_3;
    public $testo_sotto_hero;
    public $progetti;
    public function __construct($postId = null)
    {
        parent::__construct('group_6752e3f0db400', $postId ?: get_the_ID());
        $this->defineAttributes();
    }
    function defineAttributes()
    {
        $this->addField('immagine_hero');
        $this->addField('titolo_hero');
        $this->addField('highlights_frase_1');
        $this->addField('highlights_frase_2');
        $this->addField('highlights_frase_3');
        $this->addField('testo_sotto_hero');
        $this->addField('progetti');
    }
}