<?php

namespace Models;

use Core\Bases\BaseGroupAcf;

class Grazie extends BaseGroupAcf
{
    public $titolo;
    public $testo;
    public $cta;
    public $immagine;
    public function __construct($postId = null)
    {
        parent::__construct('group_67542e1849bab', $postId ?: get_the_ID());
        $this->defineAttributes();
    }
    function defineAttributes()
    {
        $this->addField('titolo');
        $this->addField('testo');
        $this->addField('cta');
        $this->addField('immagine');
    }
}