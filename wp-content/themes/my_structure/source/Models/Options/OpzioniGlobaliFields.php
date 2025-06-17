<?php

namespace Models\Options;

use Core\Bases\BaseGroupAcf;

class OpzioniGlobaliFields extends BaseGroupAcf
{
    protected $groupKey = 'group_684bbc7c2a435';
    public $immagine_hero;
    public $titolo;
    public $sottotitolo;
    public $cta;
    public $titolo_banner;
    public $sfondo_banner;
    public $sottotitolo_banner;
    public $link_banner;

    public function __construct($postId = null)
    {
        $postId = $postId ?? 'options';
        parent::__construct($this->groupKey, $postId);
        $this->defineAttributes();
    }

    public function defineAttributes()
    {
        $this->addField('immagine_hero');
        $this->addField('titolo');
        $this->addField('sottotitolo');
        $this->addField('cta');
        $this->addField('titolo_banner');
        $this->addField('sfondo_banner');
        $this->addField('sottotitolo_banner');
        $this->addField('link_banner');
    }
}
