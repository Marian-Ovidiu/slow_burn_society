<?php

namespace Models\Options;

use Core\Bases\BaseGroupAcf;

class OpzioniGlobaliFields extends BaseGroupAcf
{
    protected $groupKey = 'group_684bbc7c2a435';
    public $logo;
    public $immagine_hero;
    public $titolo;
    public $sottotitolo;
    public $cta;

    public function __construct($postId = null)
    {
        parent::__construct($this->groupKey, $postId);
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        $this->addField('logo');
        $this->addField('immagine_hero');
        $this->addField('titolo');
        $this->addField('sottotitolo');
        $this->addField('cta');
    }
}
